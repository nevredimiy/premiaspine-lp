/**
 * Codi landing map filters (state, city, doctor).
 * Expects window.WPGMP_FILTER_SOURCE and window.WPGMP_FIND_DOCTOR_MAP_ID.
 */
(function ($, window) {
	'use strict';

	var MIN_CHARS = 2;
	var ROOT = '.map-section__form';

	var SELECTOR = {
		root: ROOT,
		wrap: ROOT + ' .map-section__filter-wrap',
		input: ROOT + ' .map-section__filter-input',
		list: ROOT + ' .map-section__filter-list',
		clear: ROOT + ' .map-section__filter-clear',
		submit: ROOT + ' [data-landing-map-submit]',
		submitCount: ROOT + ' .map-section__button-count'
	};

	function normalize(s) {
		return (s || '').toString().trim().toLowerCase();
	}

	function getSource() {
		return window.WPGMP_FILTER_SOURCE || { states: [], cities: [], doctors: [] };
	}

	function getMapId() {
		return window.WPGMP_FIND_DOCTOR_MAP_ID || '';
	}

	function getMapInstance() {
		var id = getMapId();
		if (!id) return null;
		var $el = $('#map' + id);
		return $el.length ? $el.data('wpgmp_maps') : null;
	}

	function getFilterValues() {
		var state = ($('#landing-map-state').attr('data-landing-map-value') || '').trim() || ($('#landing-map-state').val() || '').trim();
		var city = ($('#landing-map-city').attr('data-landing-map-value') || '').trim() || ($('#landing-map-city').val() || '').trim();
		var doctor = ($('#landing-map-doctor').attr('data-landing-map-value') || '').trim() || ($('#landing-map-doctor').val() || '').trim();
		return { state: state, city: city, doctor: doctor };
	}

	function getStatesList() {
		return getSource().states || [];
	}

	function getCitiesList(selectedState) {
		var cities = getSource().cities || [];
		if (selectedState) {
			var s = normalize(selectedState);
			cities = cities.filter(function (c) { return normalize(c.state) === s; });
		}
		return cities.map(function (c) { return c.city; }).filter(function (v, i, a) { return a.indexOf(v) === i; });
	}

	function getDoctorsList(selectedState, selectedCity) {
		var doctors = getSource().doctors || [];
		var s = normalize(selectedState);
		var c = normalize(selectedCity);
		if (s) doctors = doctors.filter(function (d) { return normalize(d.state) === s; });
		if (c) doctors = doctors.filter(function (d) { return normalize(d.city) === c; });
		return doctors;
	}

	function getMatchingIds() {
		var vals = getFilterValues();
		var doctors = getSource().doctors || [];
		var ids = [];

		if (!vals.state && !vals.city && !vals.doctor) {
			doctors.forEach(function (d) { if (d.id != null) ids.push(d.id); });
			return ids;
		}

		var s = normalize(vals.state);
		var c = normalize(vals.city);
		var doctorVal = vals.doctor;

		doctors.forEach(function (doc) {
			var matchState = !s || normalize(doc.state) === s;
			var matchCity = !c || normalize(doc.city) === c;
			var matchDoctor = !doctorVal || (doc.id != null && (
				(/^\d+$/.test(String(doctorVal)) && String(doc.id) === String(doctorVal)) ||
				normalize(doc.title).indexOf(normalize(doctorVal)) !== -1
			));
			if (matchState && matchCity && matchDoctor && doc.id != null) {
				ids.push(doc.id);
			}
		});
		return ids;
	}

	function applyFilterToMap() {
		var mapObj = getMapInstance();
		if (!mapObj || !mapObj.places || !mapObj.map) return;

		var matchingIds = getMatchingIds();
		var idSet = {};
		matchingIds.forEach(function (id) { idSet[id] = true; });

		var bounds = null;
		for (var i = 0; i < mapObj.places.length; i++) {
			var place = mapObj.places[i];
			var show = !!idSet[place.id];
			if (place.marker) {
				place.marker.setVisible(show);
				if (show && place.location && place.location.lat != null && place.location.lng != null) {
					if (!bounds) bounds = new google.maps.LatLngBounds();
					bounds.extend(new google.maps.LatLng(parseFloat(place.location.lat), parseFloat(place.location.lng)));
				}
			}
		}

		if (bounds) {
			var ne = bounds.getNorthEast();
			var sw = bounds.getSouthWest();
			var latSpan = ne.lat() - sw.lat() || 0.01;
			var lngSpan = ne.lng() - sw.lng() || 0.01;
			var pad = 0.2;
			bounds.extend(new google.maps.LatLng(sw.lat() - latSpan * pad, sw.lng() - lngSpan * pad));
			bounds.extend(new google.maps.LatLng(ne.lat() + latSpan * pad, ne.lng() + lngSpan * pad));
			mapObj.map.fitBounds(bounds);
		}
	}

	function updateCount() {
		var ids = getMatchingIds();
		var $count = $(SELECTOR.submitCount);
		var vals = getFilterValues();
		if (!vals.state && !vals.city && !vals.doctor) {
			$count.text('').attr('aria-hidden', 'true');
			return;
		}
		$count.text('(' + ids.length + ')').attr('aria-hidden', 'false');
	}

	function updateClearButtons() {
		$(SELECTOR.wrap).each(function () {
			var $wrap = $(this);
			var $input = $wrap.find(SELECTOR.input);
			var val = ($input.attr('data-landing-map-value') || '').trim() || ($input.val() || '').trim();
			if (val) {
				$wrap.addClass('has-value');
				$wrap.find(SELECTOR.clear).addClass('is-visible');
			} else {
				$wrap.removeClass('has-value');
				$wrap.find(SELECTOR.clear).removeClass('is-visible');
			}
		});
	}

	function updateSubmitDisabled() {
		var vals = getFilterValues();
		var hasAny = !!(vals.state || vals.city || vals.doctor);
		var $btn = $(SELECTOR.submit);
		if (!$btn.length) return;
		if (hasAny) {
			$btn.prop('disabled', false).removeAttr('disabled');
		} else {
			$btn.prop('disabled', true).attr('disabled', 'disabled');
		}
	}

	function filterOptions(opts, query, isDoctor) {
		if (!query || query.length < MIN_CHARS) return opts;
		var q = normalize(query);
		return opts.filter(function (o) {
			var str = isDoctor ? (o.title || '') : (typeof o === 'string' ? o : (o.city || o));
			return normalize(str).indexOf(q) !== -1;
		});
	}

	function initSelect($wrap, $input, $list, getOptions, getLabel, fieldName, isDoctor) {
		isDoctor = !!isDoctor;
		var fieldSelector = '[data-landing-map-field="' + fieldName + '"]';

		function showList(opts) {
			$list.empty().attr('aria-hidden', 'false');
			opts.forEach(function (opt) {
				var lbl = getLabel(opt);
				var val = isDoctor ? (opt.id != null ? opt.id : opt.title) : (typeof opt === 'string' ? opt : opt);
				if (lbl === '' && isDoctor && opt.title) lbl = opt.title;
				if (val === undefined) val = lbl;
				var $li = $('<li role="option" tabindex="-1">').text(lbl).attr('data-landing-map-option', val);
				if (isDoctor) $li.attr('data-landing-map-label', lbl);
				$list.append($li);
			});
		}

		function choose(val, label) {
			$input.attr('data-landing-map-value', val);
			$input.val(label != null ? label : val);
			$list.empty().attr('aria-hidden', 'true');
			if (fieldName === 'state') {
				$(SELECTOR.wrap + '[data-landing-map-field="city"]').find(SELECTOR.input).attr('data-landing-map-value', '').val('');
				$(SELECTOR.wrap + '[data-landing-map-field="doctor"]').find(SELECTOR.input).attr('data-landing-map-value', '').val('');
			}
			if (fieldName === 'city' || fieldName === 'state') {
				$(SELECTOR.wrap + '[data-landing-map-field="doctor"]').find(SELECTOR.input).attr('data-landing-map-value', '').val('');
			}
			updateClearButtons();
			updateSubmitDisabled();
			updateCount();
		}

		$input.on('focus', function () {
			showList(filterOptions(getOptions(), $input.val().trim(), isDoctor));
		});
		$input.on('input', function () {
			var v = $(this).val().trim();
			if (v.length < MIN_CHARS) $input.attr('data-landing-map-value', '');
			showList(filterOptions(getOptions(), v, isDoctor));
			updateClearButtons();
			updateSubmitDisabled();
			updateCount();
		});
		$input.on('blur', function () {
			setTimeout(function () { $list.attr('aria-hidden', 'true'); }, 200);
		});

		$list.on('click', 'li[role="option"]', function () {
			var val = $(this).attr('data-landing-map-option');
			var label = $(this).attr('data-landing-map-label') || $(this).text();
			choose(val, label);
		});

		$wrap.find(SELECTOR.clear).on('click', function (e) {
			e.preventDefault();
			$input.attr('data-landing-map-value', '').val('');
			$list.empty().attr('aria-hidden', 'true');
			if (fieldName === 'state') {
				$(SELECTOR.wrap + '[data-landing-map-field="city"]').find(SELECTOR.input).attr('data-landing-map-value', '').val('');
				$(SELECTOR.wrap + '[data-landing-map-field="doctor"]').find(SELECTOR.input).attr('data-landing-map-value', '').val('');
			}
			if (fieldName === 'city' || fieldName === 'state') {
				$(SELECTOR.wrap + '[data-landing-map-field="doctor"]').find(SELECTOR.input).attr('data-landing-map-value', '').val('');
			}
			updateClearButtons();
			updateSubmitDisabled();
			updateCount();
		});
	}

	var initialized = false;

	function initFields() {
		if (initialized) return true;
		if (!$(SELECTOR.root).length) return false;
		var src = getSource();
		if (!src.doctors || !src.doctors.length || !getMapId()) return false;

		initSelect(
			$(SELECTOR.wrap + '[data-landing-map-field="state"]'),
			$('#landing-map-state'),
			$(SELECTOR.wrap + '[data-landing-map-field="state"]').find(SELECTOR.list),
			getStatesList,
			function (o) { return o; },
			'state'
		);
		initSelect(
			$(SELECTOR.wrap + '[data-landing-map-field="city"]'),
			$('#landing-map-city'),
			$(SELECTOR.wrap + '[data-landing-map-field="city"]').find(SELECTOR.list),
			function () { return getCitiesList(getFilterValues().state); },
			function (o) { return o; },
			'city'
		);
		initSelect(
			$(SELECTOR.wrap + '[data-landing-map-field="doctor"]'),
			$('#landing-map-doctor'),
			$(SELECTOR.wrap + '[data-landing-map-field="doctor"]').find(SELECTOR.list),
			function () { return getDoctorsList(getFilterValues().state, getFilterValues().city); },
			function (d) { return d && d.title ? d.title : ''; },
			'doctor',
			true
		);

		updateClearButtons();
		updateSubmitDisabled();

		$(SELECTOR.submit).off('click.landingMapFilters').on('click.landingMapFilters', function () {
			if ($(this).prop('disabled')) return;
			applyFilterToMap();
		});

		initialized = true;
		return true;
	}

	function boot() {
		var attempts = 0;
		var maxAttempts = 40;
		var iv = setInterval(function () {
			attempts++;
			if (initFields() || attempts >= maxAttempts) {
				clearInterval(iv);
			}
		}, 250);
	}

	$(boot);
})(jQuery, window);
