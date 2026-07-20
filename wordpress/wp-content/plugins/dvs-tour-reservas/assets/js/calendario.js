/**
 * Calendario de reservas DVS Tour Reservas.
 * Muestra disponibilidad mensual, permite elegir tour (Termas / Embalse),
 * registra la reserva vía AJAX y redirige al Botón de Pago del Banco de Chile.
 *
 * Trilingüe: español, inglés y portugués. El visitante cambia de idioma
 * con los botones ES / EN / PT y la elección se recuerda en localStorage.
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
	var infoSeleccion = null; // disponibilidad del día abierto en el panel

	var idioma = (function () {
		try {
			var guardado = window.localStorage.getItem('dvsTrIdioma');
			if (guardado && cfg.i18n[guardado]) {
				return guardado;
			}
		} catch (e) { /* localStorage bloqueado */ }
		return cfg.i18n[cfg.idioma] ? cfg.idioma : 'es';
	})();

	function T() {
		return cfg.i18n[idioma];
	}

	var elMes = app.querySelector('.dvs-tr-mes');
	var elGrilla = app.querySelector('.dvs-tr-grilla');
	var elEstado = app.querySelector('.dvs-tr-estado');
	var elPanel = app.querySelector('.dvs-tr-panel');
	var elPanelFecha = app.querySelector('.dvs-tr-panel-fecha');
	var elOpciones = app.querySelector('.dvs-tr-opciones');
	var elForm = app.querySelector('.dvs-tr-form');
	var elFormTitulo = app.querySelector('.dvs-tr-form-titulo');

	// Selector de idioma
	app.querySelectorAll('.dvs-tr-idioma').forEach(function (btn) {
		btn.addEventListener('click', function () {
			var nuevo = btn.getAttribute('data-lang');
			if (!cfg.i18n[nuevo] || nuevo === idioma) {
				return;
			}
			idioma = nuevo;
			try { window.localStorage.setItem('dvsTrIdioma', idioma); } catch (e) { /* sin persistencia */ }
			aplicarIdioma();
			// Avisa al traductor global del sitio (botón flotante 🌐).
			window.dispatchEvent(new CustomEvent('dvsTrIdioma', { detail: idioma }));
		});
	});

	// Cambios hechos desde el botón flotante 🌐 del sitio.
	window.addEventListener('dvsTrIdioma', function (ev) {
		var nuevo = ev.detail;
		if (cfg.i18n[nuevo] && nuevo !== idioma) {
			idioma = nuevo;
			aplicarIdioma();
		}
	});

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

	function aplicarIdioma() {
		// Botón activo
		app.querySelectorAll('.dvs-tr-idioma').forEach(function (b) {
			b.classList.toggle('dvs-tr-idioma--activo', b.getAttribute('data-lang') === idioma);
		});
		// Textos estáticos marcados con data-dvs-i18n
		app.querySelectorAll('[data-dvs-i18n]').forEach(function (el) {
			var clave = el.getAttribute('data-dvs-i18n');
			if (T()[clave]) {
				el.textContent = T()[clave];
			}
		});
		// Accesibilidad de la navegación de mes
		var navs = app.querySelectorAll('.dvs-tr-nav');
		if (navs[0]) { navs[0].setAttribute('aria-label', T().mes_anterior); }
		if (navs[1]) { navs[1].setAttribute('aria-label', T().mes_siguiente); }

		elMes.textContent = T().meses[vista.mes - 1] + ' ' + vista.anio;
		pintarGrilla();

		// Si el panel está abierto, repintarlo en el nuevo idioma
		if (!elPanel.hidden && infoSeleccion) {
			var previa = seleccion ? seleccion.tour : null;
			abrirPanel(infoSeleccion.fecha, infoSeleccion.info, previa);
		}
	}

	function clp(n) {
		return '$' + Number(n).toLocaleString('es-CL');
	}

	function estado(msg, esError) {
		elEstado.textContent = msg || '';
		elEstado.classList.toggle('dvs-tr-estado--error', !!esError);
	}

	function cargarMes() {
		estado(T().cargando);
		elMes.textContent = T().meses[vista.mes - 1] + ' ' + vista.anio;
		elGrilla.innerHTML = '';

		var url = cfg.ajaxUrl + '?action=dvs_tr_disponibilidad&nonce=' + encodeURIComponent(cfg.nonce) +
			'&anio=' + vista.anio + '&mes=' + vista.mes;

		fetch(url, { credentials: 'same-origin' })
			.then(function (r) { return r.json(); })
			.then(function (json) {
				if (!json || !json.success) {
					throw new Error((json && json.data && json.data.mensaje) || T().error_red);
				}
				datosMes = json.data.dias;
				estado('');
				pintarGrilla();
			})
			.catch(function (e) {
				estado(e.message || T().error_red, true);
			});
	}

	function pintarGrilla() {
		elGrilla.innerHTML = '';

		T().dias.forEach(function (d) {
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

			var totalTours = Object.keys(T().tours).length;
			if (info.disponibles.length === 0) {
				celda.classList.add('dvs-tr-dia--lleno');
				celda.disabled = true;
				if (info.ocupados.length > 0 && info.ocupados.length < totalTours) {
					celda.classList.add('dvs-tr-dia--guiaocupado');
					celda.title = T().guia_ocupado;
				}
			} else if (info.disponibles.length < totalTours) {
				celda.classList.add('dvs-tr-dia--parcial');
			} else {
				celda.classList.add('dvs-tr-dia--libre');
			}

			celda.addEventListener('click', function () {
				abrirPanel(fecha, info, null);
			});
			elGrilla.appendChild(celda);
		});
	}

	function abrirPanel(fecha, info, tourPreseleccionado) {
		seleccion = null;
		infoSeleccion = { fecha: fecha, info: info };
		elForm.hidden = true;
		elPanel.hidden = false;
		elPanelFecha.textContent = formatoFecha(fecha);
		elOpciones.innerHTML = '';

		Object.keys(T().tours).forEach(function (clave) {
			var tour = T().tours[clave];
			var precio = cfg.precios[clave] || 0;
			var disponible = info.disponibles.indexOf(clave) !== -1;

			var caja = document.createElement('button');
			caja.type = 'button';
			caja.className = 'dvs-tr-tour' + (disponible ? '' : ' dvs-tr-tour--nodisp');
			caja.disabled = !disponible;

			var motivo = '';
			if (!disponible) {
				motivo = info.ocupados.indexOf(clave) !== -1 ? T().reservado : T().guia_otro_tour;
			}

			caja.innerHTML =
				'<strong>' + tour.nombre + '</strong>' +
				'<span>' + tour.descripcion + '</span>' +
				(precio > 0 ? '<span class="dvs-tr-precio">' + clp(precio) + ' CLP</span>' : '') +
				(motivo ? '<span class="dvs-tr-motivo">' + motivo + '</span>' : '');

			if (disponible) {
				caja.addEventListener('click', function () {
					seleccionarTour(clave, fecha, caja);
				});
			}
			elOpciones.appendChild(caja);

			if (disponible && tourPreseleccionado === clave) {
				seleccionarTour(clave, fecha, caja);
			}
		});

		if (!tourPreseleccionado) {
			elPanel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
		}
	}

	function seleccionarTour(clave, fecha, caja) {
		seleccion = { fecha: fecha, tour: clave };
		elOpciones.querySelectorAll('.dvs-tr-tour').forEach(function (b) {
			b.classList.remove('dvs-tr-tour--activo');
		});
		caja.classList.add('dvs-tr-tour--activo');
		elFormTitulo.textContent = T().tours[clave].nombre + ' — ' + formatoFecha(fecha);
		elForm.hidden = false;
		elForm.personas.max = cfg.maxPersonas;
		elForm.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
	}

	function cerrarPanel() {
		seleccion = null;
		infoSeleccion = null;
		elPanel.hidden = true;
		elForm.hidden = true;
	}

	function formatoFecha(iso) {
		var p = iso.split('-');
		var dia = parseInt(p[2], 10);
		var mes = T().meses[parseInt(p[1], 10) - 1];
		if (idioma === 'en') {
			return mes + ' ' + dia + ', ' + p[0];
		}
		return dia + ' de ' + mes + ' de ' + p[0];
	}

	elForm.addEventListener('submit', function (ev) {
		ev.preventDefault();
		if (!seleccion) {
			return;
		}

		var btn = elForm.querySelector('.dvs-tr-btn-pagar');
		btn.disabled = true;
		estado(T().cargando);

		var datos = new FormData();
		datos.append('action', 'dvs_tr_reservar');
		datos.append('nonce', cfg.nonce);
		datos.append('tour', seleccion.tour);
		datos.append('fecha', seleccion.fecha);
		datos.append('nombre', elForm.nombre.value);
		datos.append('email', elForm.email.value);
		datos.append('telefono', elForm.telefono.value);
		datos.append('personas', elForm.personas.value);
		datos.append('idioma', idioma);

		fetch(cfg.ajaxUrl, { method: 'POST', credentials: 'same-origin', body: datos })
			.then(function (r) { return r.json(); })
			.then(function (json) {
				if (!json || !json.success) {
					var datosErr = (json && json.data) || {};
					// Si el servidor entrega un código conocido, usar el texto traducido.
					var msg = (datosErr.codigo && T()[datosErr.codigo]) || datosErr.mensaje || T().error_red;
					throw new Error(msg);
				}
				if (json.data.pagoUrl) {
					estado(T().redirigiendo);
					window.location.href = json.data.pagoUrl;
				} else {
					estado(T().sin_pago);
					cerrarPanel();
					cargarMes();
				}
			})
			.catch(function (e) {
				btn.disabled = false;
				estado(e.message || T().error_red, true);
				cargarMes(); // Refresca por si el cupo se ocupó mientras tanto.
			});
	});

	aplicarIdioma();
	cargarMes();
})();
