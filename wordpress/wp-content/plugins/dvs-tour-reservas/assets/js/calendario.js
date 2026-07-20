/**
 * Calendario de reservas DVS Tour Reservas.
 * Muestra disponibilidad mensual, permite elegir tour (Termas / Embalse),
 * registra la reserva vía AJAX y redirige al Botón de Pago del Banco de Chile.
 */
(function () {
	'use strict';

	var app = document.getElementById('dvs-tr-app');
	if (!app || typeof dvsTrConfig === 'undefined') {
		return;
	}

	var cfg = dvsTrConfig;
	var hoy = new Date();
	var vista = { anio: hoy.getFullYear(), mes: hoy.getMonth() + 1 }; // mes 1-12
	var datosMes = {};
	var seleccion = null; // { fecha, tour }

	var elMes = app.querySelector('.dvs-tr-mes');
	var elGrilla = app.querySelector('.dvs-tr-grilla');
	var elEstado = app.querySelector('.dvs-tr-estado');
	var elPanel = app.querySelector('.dvs-tr-panel');
	var elPanelFecha = app.querySelector('.dvs-tr-panel-fecha');
	var elOpciones = app.querySelector('.dvs-tr-opciones');
	var elForm = app.querySelector('.dvs-tr-form');
	var elFormTitulo = app.querySelector('.dvs-tr-form-titulo');

	app.querySelectorAll('.dvs-tr-nav').forEach(function (btn) {
		btn.addEventListener('click', function () {
			var dir = parseInt(btn.getAttribute('data-dir'), 10);
			vista.mes += dir;
			if (vista.mes < 1) { vista.mes = 12; vista.anio--; }
			if (vista.mes > 12) { vista.mes = 1; vista.anio++; }
			cerrarPanel();
			cargarMes();
		});
	});

	function clp(n) {
		return '$' + Number(n).toLocaleString('es-CL');
	}

	function estado(msg, esError) {
		elEstado.textContent = msg || '';
		elEstado.classList.toggle('dvs-tr-estado--error', !!esError);
	}

	function cargarMes() {
		estado(cfg.i18n.cargando);
		elMes.textContent = cfg.i18n.meses[vista.mes - 1] + ' ' + vista.anio;
		elGrilla.innerHTML = '';

		var url = cfg.ajaxUrl + '?action=dvs_tr_disponibilidad&nonce=' + encodeURIComponent(cfg.nonce) +
			'&anio=' + vista.anio + '&mes=' + vista.mes;

		fetch(url, { credentials: 'same-origin' })
			.then(function (r) { return r.json(); })
			.then(function (json) {
				if (!json || !json.success) {
					throw new Error((json && json.data && json.data.mensaje) || cfg.i18n.errorRed);
				}
				datosMes = json.data.dias;
				estado('');
				pintarGrilla();
			})
			.catch(function (e) {
				estado(e.message || cfg.i18n.errorRed, true);
			});
	}

	function pintarGrilla() {
		elGrilla.innerHTML = '';

		cfg.i18n.dias.forEach(function (d) {
			var h = document.createElement('div');
			h.className = 'dvs-tr-diasemana';
			h.textContent = d;
			elGrilla.appendChild(h);
		});

		var primerDia = new Date(vista.anio, vista.mes - 1, 1);
		// Lunes = 0 … Domingo = 6
		var offset = (primerDia.getDay() + 6) % 7;
		for (var i = 0; i < offset; i++) {
			var vacio = document.createElement('div');
			vacio.className = 'dvs-tr-dia dvs-tr-dia--vacio';
			elGrilla.appendChild(vacio);
		}

		Object.keys(datosMes).sort().forEach(function (fecha) {
			var info = datosMes[fecha];
			var celda = document.createElement('button');
			celda.type = 'button';
			celda.className = 'dvs-tr-dia';
			celda.textContent = parseInt(fecha.slice(8), 10);
			celda.setAttribute('data-fecha', fecha);

			var totalTours = Object.keys(cfg.tours).length;
			if (info.disponibles.length === 0) {
				celda.classList.add('dvs-tr-dia--lleno');
				celda.disabled = true;
				if (info.ocupados.length > 0 && info.ocupados.length < totalTours) {
					celda.classList.add('dvs-tr-dia--guiaocupado');
					celda.title = 'Guía ocupado este día';
				}
			} else if (info.disponibles.length < totalTours) {
				celda.classList.add('dvs-tr-dia--parcial');
			} else {
				celda.classList.add('dvs-tr-dia--libre');
			}

			celda.addEventListener('click', function () {
				abrirPanel(fecha, info);
			});
			elGrilla.appendChild(celda);
		});
	}

	function abrirPanel(fecha, info) {
		seleccion = null;
		elForm.hidden = true;
		elPanel.hidden = false;
		elPanelFecha.textContent = formatoFecha(fecha);
		elOpciones.innerHTML = '';

		Object.keys(cfg.tours).forEach(function (clave) {
			var tour = cfg.tours[clave];
			var disponible = info.disponibles.indexOf(clave) !== -1;

			var caja = document.createElement('button');
			caja.type = 'button';
			caja.className = 'dvs-tr-tour' + (disponible ? '' : ' dvs-tr-tour--nodisp');
			caja.disabled = !disponible;

			var motivo = '';
			if (!disponible) {
				motivo = info.ocupados.indexOf(clave) !== -1
					? 'Reservado'
					: 'No disponible: el guía está en el otro tour este día';
			}

			caja.innerHTML =
				'<strong>' + tour.nombre + '</strong>' +
				'<span>' + tour.descripcion + '</span>' +
				(tour.precio > 0 ? '<span class="dvs-tr-precio">' + clp(tour.precio) + '</span>' : '') +
				(motivo ? '<span class="dvs-tr-motivo">' + motivo + '</span>' : '');

			if (disponible) {
				caja.addEventListener('click', function () {
					seleccion = { fecha: fecha, tour: clave };
					elOpciones.querySelectorAll('.dvs-tr-tour').forEach(function (b) {
						b.classList.remove('dvs-tr-tour--activo');
					});
					caja.classList.add('dvs-tr-tour--activo');
					elFormTitulo.textContent = tour.nombre + ' — ' + formatoFecha(fecha);
					elForm.hidden = false;
					elForm.personas.max = cfg.maxPersonas;
					elForm.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
				});
			}
			elOpciones.appendChild(caja);
		});

		elPanel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
	}

	function cerrarPanel() {
		seleccion = null;
		elPanel.hidden = true;
		elForm.hidden = true;
	}

	function formatoFecha(iso) {
		var p = iso.split('-');
		return p[2] + ' de ' + cfg.i18n.meses[parseInt(p[1], 10) - 1] + ' de ' + p[0];
	}

	elForm.addEventListener('submit', function (ev) {
		ev.preventDefault();
		if (!seleccion) {
			return;
		}

		var btn = elForm.querySelector('.dvs-tr-btn-pagar');
		btn.disabled = true;
		estado(cfg.i18n.cargando);

		var datos = new FormData();
		datos.append('action', 'dvs_tr_reservar');
		datos.append('nonce', cfg.nonce);
		datos.append('tour', seleccion.tour);
		datos.append('fecha', seleccion.fecha);
		datos.append('nombre', elForm.nombre.value);
		datos.append('email', elForm.email.value);
		datos.append('telefono', elForm.telefono.value);
		datos.append('personas', elForm.personas.value);

		fetch(cfg.ajaxUrl, { method: 'POST', credentials: 'same-origin', body: datos })
			.then(function (r) { return r.json(); })
			.then(function (json) {
				if (!json || !json.success) {
					throw new Error((json && json.data && json.data.mensaje) || cfg.i18n.errorRed);
				}
				if (json.data.pagoUrl) {
					estado(cfg.i18n.redirigiendo);
					window.location.href = json.data.pagoUrl;
				} else {
					estado(cfg.i18n.sinPago);
					cerrarPanel();
					cargarMes();
				}
			})
			.catch(function (e) {
				btn.disabled = false;
				estado(e.message || cfg.i18n.errorRed, true);
				cargarMes(); // Refresca por si el cupo se ocupó mientras tanto.
			});
	});

	cargarMes();
})();
