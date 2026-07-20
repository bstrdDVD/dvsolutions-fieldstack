/**
 * Traductor global del sitio (ES / EN / PT).
 *
 * Crea un botón flotante 🌐 en la esquina superior derecha. Al elegir idioma
 * recorre el texto visible de la página y lo reemplaza con el diccionario del
 * plugin, guardando los originales para poder volver al español al instante.
 * La elección se recuerda en localStorage y se sincroniza con el calendario
 * de reservas mediante el evento "dvsTrIdioma".
 */
(function () {
	'use strict';

	if (typeof dvsTrTraductor === 'undefined') {
		return;
	}

	var IDIOMAS = [
		{ cod: 'es', bandera: '🇨🇱', nombre: 'Español' },
		{ cod: 'en', bandera: '🇬🇧', nombre: 'English' },
		{ cod: 'pt', bandera: '🇧🇷', nombre: 'Português' }
	];
	var ATRIBUTOS = ['placeholder', 'title', 'alt', 'aria-label'];

	function norm(s) {
		return s.replace(/\s+/g, ' ').trim();
	}

	// Índices de traducción por idioma destino.
	var mapa = { en: {}, pt: {} };
	Object.keys(dvsTrTraductor.diccionario).forEach(function (es) {
		var trad = dvsTrTraductor.diccionario[es];
		var clave = norm(es);
		if (trad.en) { mapa.en[clave] = trad.en; }
		if (trad.pt) { mapa.pt[clave] = trad.pt; }
	});

	var idioma = (function () {
		try {
			var guardado = window.localStorage.getItem('dvsTrIdioma');
			if (guardado === 'en' || guardado === 'pt' || guardado === 'es') {
				return guardado;
			}
		} catch (e) { /* localStorage bloqueado */ }
		return 'es';
	})();

	// Originales guardados para restaurar el español sin recargar.
	var textosOriginales = new WeakMap();
	var atributosOriginales = new WeakMap();

	function traducirTexto(nodo) {
		var original = textosOriginales.get(nodo);
		if (original === undefined) {
			original = nodo.nodeValue;
			textosOriginales.set(nodo, original);
		}
		if (idioma === 'es') {
			if (nodo.nodeValue !== original) { nodo.nodeValue = original; }
			return;
		}
		var clave = norm(original);
		if (!clave) { return; }
		var traduccion = mapa[idioma][clave];
		if (traduccion !== undefined) {
			// Conserva los espacios originales alrededor del texto.
			var pre = original.match(/^\s*/)[0];
			var post = original.match(/\s*$/)[0];
			nodo.nodeValue = pre + traduccion + post;
		} else if (nodo.nodeValue !== original) {
			nodo.nodeValue = original;
		}
	}

	function traducirAtributos(el) {
		var originales = atributosOriginales.get(el);
		if (originales === undefined) {
			originales = {};
			ATRIBUTOS.forEach(function (attr) {
				if (el.hasAttribute(attr)) {
					originales[attr] = el.getAttribute(attr);
				}
			});
			atributosOriginales.set(el, originales);
		}
		Object.keys(originales).forEach(function (attr) {
			var original = originales[attr];
			if (idioma === 'es') {
				el.setAttribute(attr, original);
				return;
			}
			var traduccion = mapa[idioma][norm(original)];
			el.setAttribute(attr, traduccion !== undefined ? traduccion : original);
		});
	}

	function esNodoTraducible(nodo) {
		var p = nodo.parentNode;
		if (!p || !p.nodeName) { return false; }
		var tag = p.nodeName;
		if (tag === 'SCRIPT' || tag === 'STYLE' || tag === 'NOSCRIPT' || tag === 'TEXTAREA') {
			return false;
		}
		// El calendario de reservas y este selector se traducen solos.
		if (p.closest && (p.closest('#dvs-tr-app') || p.closest('.dvs-trad'))) {
			return false;
		}
		return true;
	}

	function traducirTodo(raiz) {
		raiz = raiz || document.body;
		if (raiz.nodeType === Node.TEXT_NODE) {
			if (esNodoTraducible(raiz)) { traducirTexto(raiz); }
			return;
		}
		if (raiz.nodeType !== Node.ELEMENT_NODE) { return; }

		var walker = document.createTreeWalker(raiz, NodeFilter.SHOW_TEXT, {
			acceptNode: function (n) {
				return esNodoTraducible(n) ? NodeFilter.FILTER_ACCEPT : NodeFilter.FILTER_REJECT;
			}
		});
		var nodos = [];
		while (walker.nextNode()) { nodos.push(walker.currentNode); }
		nodos.forEach(traducirTexto);

		var selector = ATRIBUTOS.map(function (a) { return '[' + a + ']'; }).join(',');
		if (raiz.matches && raiz.matches(selector)) { traducirAtributos(raiz); }
		raiz.querySelectorAll(selector).forEach(function (el) {
			if (!el.closest('#dvs-tr-app') && !el.closest('.dvs-trad')) {
				traducirAtributos(el);
			}
		});
	}

	function cambiarIdioma(nuevo, notificar) {
		idioma = nuevo;
		try { window.localStorage.setItem('dvsTrIdioma', idioma); } catch (e) { /* sin persistencia */ }
		document.documentElement.setAttribute('lang', idioma === 'es' ? 'es-CL' : idioma);
		traducirTodo();
		actualizarBoton();
		if (notificar) {
			window.dispatchEvent(new CustomEvent('dvsTrIdioma', { detail: idioma }));
		}
	}

	// ——— Botón flotante ———

	var contenedor, botonPrincipal, menu;

	function idiomaActual() {
		for (var i = 0; i < IDIOMAS.length; i++) {
			if (IDIOMAS[i].cod === idioma) { return IDIOMAS[i]; }
		}
		return IDIOMAS[0];
	}

	function actualizarBoton() {
		if (!botonPrincipal) { return; }
		botonPrincipal.innerHTML = '🌐 <span>' + idioma.toUpperCase() + '</span>';
		menu.querySelectorAll('button').forEach(function (b) {
			b.classList.toggle('dvs-trad-activo', b.getAttribute('data-lang') === idioma);
		});
	}

	function crearBoton() {
		contenedor = document.createElement('div');
		contenedor.className = 'dvs-trad';

		botonPrincipal = document.createElement('button');
		botonPrincipal.type = 'button';
		botonPrincipal.className = 'dvs-trad-btn';
		botonPrincipal.setAttribute('aria-label', 'Idioma / Language / Idioma');
		botonPrincipal.addEventListener('click', function () {
			menu.hidden = !menu.hidden;
		});

		menu = document.createElement('div');
		menu.className = 'dvs-trad-menu';
		menu.hidden = true;

		IDIOMAS.forEach(function (lang) {
			var b = document.createElement('button');
			b.type = 'button';
			b.setAttribute('data-lang', lang.cod);
			b.innerHTML = lang.bandera + ' ' + lang.nombre;
			b.addEventListener('click', function () {
				menu.hidden = true;
				if (lang.cod !== idioma) {
					cambiarIdioma(lang.cod, true);
				}
			});
			menu.appendChild(b);
		});

		document.addEventListener('click', function (ev) {
			if (!contenedor.contains(ev.target)) {
				menu.hidden = true;
			}
		});

		contenedor.appendChild(botonPrincipal);
		contenedor.appendChild(menu);
		document.body.appendChild(contenedor);
		actualizarBoton();
	}

	// ——— Arranque ———

	function iniciar() {
		crearBoton();

		// El calendario tiene su propio selector: se oculta para no duplicar.
		document.querySelectorAll('.dvs-tr-idiomas').forEach(function (el) {
			el.style.display = 'none';
		});

		if (idioma !== 'es') {
			traducirTodo();
			document.documentElement.setAttribute('lang', idioma);
		}

		// Contenido que aparece después (sliders, lightboxes, etc.).
		var observador = new MutationObserver(function (mutaciones) {
			if (idioma === 'es') { return; }
			mutaciones.forEach(function (m) {
				m.addedNodes.forEach(function (n) { traducirTodo(n); });
			});
		});
		observador.observe(document.body, { childList: true, subtree: true });

		// Sincronización con el selector interno del calendario (si visible).
		window.addEventListener('dvsTrIdioma', function (ev) {
			var nuevo = ev.detail;
			if ((nuevo === 'es' || nuevo === 'en' || nuevo === 'pt') && nuevo !== idioma) {
				cambiarIdioma(nuevo, false);
			}
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', iniciar);
	} else {
		iniciar();
	}
})();
