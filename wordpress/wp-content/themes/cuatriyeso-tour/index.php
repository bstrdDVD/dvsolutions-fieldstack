<?php
/**
 * Template Name: Inicio (Homepage) - Actualizado Invierno 2026
 * Description: CuatriYesoTour Homepage - Camino al Embalse + Termas reabre en octubre
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CuatriYesoTour - Tours en Cuatrimoto</title>
    <style>
        :root {
            --color-primary: #d4a574;
            --color-dark: #1a1a1a;
            --color-light: #f8f8f8;
            --color-text: #333;
            --color-text-light: #666;
            --color-border: #ddd;
            --color-success: #4caf50;
            --color-danger: #f44336;
            --color-warning: #ff9800;
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --color-dark: #0d0d0d;
                --color-light: #1a1a1a;
                --color-text: #f0f0f0;
                --color-text-light: #b0b0b0;
                --color-border: #333;
            }
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--color-text);
            background-color: var(--color-light);
            line-height: 1.6;
        }

        .page {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Header & Navigation */
        header {
            background: var(--color-dark);
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .logo {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--color-primary);
        }

        nav {
            display: flex;
            gap: 2rem;
        }

        nav a {
            color: white;
            text-decoration: none;
            transition: color 0.3s;
        }

        nav a:hover {
            color: var(--color-primary);
        }

        @media (max-width: 768px) {
            header {
                flex-direction: column;
                gap: 1rem;
            }
            nav {
                gap: 1rem;
                font-size: 0.9rem;
            }
        }

        /* Hero Section */
        .hero {
            position: relative;
            background: linear-gradient(135deg, #1a1a1a 0%, #333 100%);
            color: white;
            padding: 4rem 2rem;
            text-align: center;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 600"><defs><pattern id="topo" x="0" y="0" width="200" height="200" patternUnits="userSpaceOnUse"><path d="M0,100 Q50,80 100,100 T200,100" fill="none" stroke="rgba(212,165,116,0.1)" stroke-width="2"/><path d="M0,120 Q50,100 100,120 T200,120" fill="none" stroke="rgba(212,165,116,0.1)" stroke-width="1"/></pattern></defs><rect width="1200" height="600" fill="none"/><rect width="1200" height="600" fill="url(%23topo)"/></svg>');
            opacity: 0.3;
        }

        .hero-content {
            position: relative;
            z-index: 1;
            max-width: 800px;
            margin: 0 auto;
        }

        .hero h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }

        .hero p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        .btn-primary {
            display: inline-block;
            background: var(--color-primary);
            color: var(--color-dark);
            padding: 0.75rem 2rem;
            border-radius: 4px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }

        .btn-primary:hover {
            background: #c69060;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(212,165,116,0.3);
        }

        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2rem;
            }
            .hero p {
                font-size: 1rem;
            }
        }

        /* Alert Banner */
        .alert-banner {
            background: var(--color-warning);
            color: white;
            padding: 1.5rem 2rem;
            text-align: center;
            font-weight: bold;
        }

        .alert-banner p {
            margin: 0.5rem 0;
        }

        /* Tours Section */
        .tours-section {
            padding: 3rem 2rem;
            background: var(--color-light);
        }

        .section-title {
            text-align: center;
            font-size: 2rem;
            color: var(--color-primary);
            margin-bottom: 2rem;
        }

        .tours-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .tour-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            position: relative;
        }

        .tour-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }

        .tour-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: var(--color-success);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: bold;
            z-index: 10;
        }

        .tour-badge.coming-soon {
            background: var(--color-warning);
        }

        @media (prefers-color-scheme: dark) {
            .tour-card {
                background: var(--color-light);
                box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            }
            .tour-card:hover {
                box-shadow: 0 8px 20px rgba(0,0,0,0.4);
            }
        }

        .tour-image {
            width: 100%;
            height: 250px;
            background-size: cover;
            background-position: center;
            position: relative;
        }

        .tour-image::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to bottom, transparent 0%, rgba(0,0,0,0.3) 100%);
        }

        .tour-info {
            padding: 1.5rem;
        }

        .tour-info h3 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            color: var(--color-primary);
        }

        .tour-info p {
            color: var(--color-text-light);
            margin-bottom: 1rem;
            font-size: 0.95rem;
        }

        .tour-times {
            background: #f0f0f0;
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }

        @media (prefers-color-scheme: dark) {
            .tour-times {
                background: #262626;
            }
        }

        .tour-time-slot {
            margin-bottom: 0.5rem;
        }

        .tour-time-slot:last-child {
            margin-bottom: 0;
        }

        .tour-details {
            list-style: none;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            color: var(--color-text-light);
        }

        .tour-details li {
            padding: 0.3rem 0;
        }

        .tour-details li::before {
            content: '✓ ';
            color: var(--color-success);
            font-weight: bold;
            margin-right: 0.5rem;
        }

        .tour-price {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--color-primary);
            margin-bottom: 1rem;
        }

        .btn-book {
            background: var(--color-primary);
            color: var(--color-dark);
            padding: 0.6rem 1.5rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s;
            width: 100%;
            font-size: 0.95rem;
            text-decoration: none;
            display: block;
            text-align: center;
        }

        .btn-book:hover {
            background: #c69060;
            transform: translateY(-2px);
        }

        .btn-book:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }

        /* Gallery Section */
        .gallery-section {
            padding: 3rem 2rem;
            background: white;
        }

        @media (prefers-color-scheme: dark) {
            .gallery-section {
                background: var(--color-light);
            }
        }

        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-top: 2rem;
        }

        .gallery-item {
            position: relative;
            overflow: hidden;
            border-radius: 8px;
            aspect-ratio: 1;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }

        .gallery-item:hover {
            transform: scale(1.05);
        }

        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Testimonials Section */
        .testimonials-section {
            padding: 3rem 2rem;
            background: var(--color-light);
        }

        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .testimonial-card {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        @media (prefers-color-scheme: dark) {
            .testimonial-card {
                background: #1f1f1f;
                box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            }
        }

        .star-rating {
            color: #ffc107;
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
        }

        .testimonial-text {
            color: var(--color-text-light);
            font-style: italic;
            margin-bottom: 1rem;
            line-height: 1.6;
        }

        .testimonial-author {
            font-weight: bold;
            color: var(--color-primary);
        }

        /* Stats Section */
        .stats-section {
            padding: 3rem 2rem;
            background: white;
        }

        @media (prefers-color-scheme: dark) {
            .stats-section {
                background: var(--color-light);
            }
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .stat-card {
            text-align: center;
            padding: 2rem;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--color-primary);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: var(--color-text-light);
            font-size: 1rem;
        }

        /* CTA Section */
        .cta-section {
            background: white;
            padding: 3rem 2rem;
            margin: 0;
            border-radius: 0;
            box-shadow: none;
            text-align: center;
        }

        @media (prefers-color-scheme: dark) {
            .cta-section {
                background: var(--color-light);
            }
        }

        .cta-section h2 {
            color: var(--color-primary);
            margin-bottom: 1rem;
            font-size: 2rem;
        }

        .cta-section p {
            color: var(--color-text-light);
            margin-bottom: 2rem;
            font-size: 1.1rem;
        }

        /* Inclusions Section */
        .inclusions-section {
            padding: 3rem 2rem;
            background: white;
        }

        @media (prefers-color-scheme: dark) {
            .inclusions-section {
                background: var(--color-light);
            }
        }

        .inclusions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .inclusion-card {
            text-align: center;
            padding: 2rem;
            background: var(--color-light);
            border-radius: 8px;
        }

        @media (prefers-color-scheme: dark) {
            .inclusion-card {
                background: #262626;
            }
        }

        .inclusion-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .inclusion-card h4 {
            color: var(--color-primary);
            margin-bottom: 0.5rem;
            font-size: 1.1rem;
        }

        .inclusion-card p {
            color: var(--color-text-light);
            font-size: 0.9rem;
        }

        /* Restrictions Section */
        .restrictions-section {
            padding: 3rem 2rem;
            background: var(--color-light);
        }

        .restrictions-box {
            background: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 8px;
            padding: 2rem;
            margin: 2rem 0;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }

        @media (prefers-color-scheme: dark) {
            .restrictions-box {
                background: #664d03;
                border-color: #997404;
                color: #fff3cd;
            }
        }

        .restrictions-box h3 {
            color: #856404;
            margin-bottom: 1rem;
        }

        @media (prefers-color-scheme: dark) {
            .restrictions-box h3 {
                color: #ffc107;
            }
        }

        .restrictions-box ul {
            list-style-position: inside;
            color: #856404;
        }

        @media (prefers-color-scheme: dark) {
            .restrictions-box ul {
                color: #fff3cd;
            }
        }

        .restrictions-box li {
            margin-bottom: 0.5rem;
        }

        /* FAQ Section */
        .faq-section {
            padding: 3rem 2rem;
            background: white;
        }

        @media (prefers-color-scheme: dark) {
            .faq-section {
                background: var(--color-light);
            }
        }

        .faq-container {
            max-width: 800px;
            margin: 2rem auto;
        }

        .faq-item {
            margin-bottom: 1rem;
            border: 1px solid var(--color-border);
            border-radius: 8px;
            overflow: hidden;
        }

        .faq-question {
            background: var(--color-light);
            padding: 1.5rem;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            user-select: none;
            transition: background 0.3s;
        }

        @media (prefers-color-scheme: dark) {
            .faq-question {
                background: #262626;
            }
        }

        .faq-question:hover {
            background: #e8e8e8;
        }

        @media (prefers-color-scheme: dark) {
            .faq-question:hover {
                background: #333;
            }
        }

        .faq-question h4 {
            color: var(--color-primary);
            font-size: 1.1rem;
        }

        .faq-toggle {
            font-size: 1.5rem;
            transition: transform 0.3s;
        }

        .faq-item.active .faq-toggle {
            transform: rotate(45deg);
        }

        .faq-answer {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }

        .faq-item.active .faq-answer {
            max-height: 500px;
        }

        .faq-answer p {
            padding: 1.5rem;
            color: var(--color-text-light);
            line-height: 1.6;
        }

        /* Contact Section */
        .contact-section {
            padding: 3rem 2rem;
            background: white;
        }

        @media (prefers-color-scheme: dark) {
            .contact-section {
                background: var(--color-light);
            }
        }

        .contact-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            max-width: 1000px;
            margin: 0 auto;
        }

        @media (max-width: 768px) {
            .contact-container {
                grid-template-columns: 1fr;
            }
        }

        .contact-info h3 {
            color: var(--color-primary);
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
        }

        .contact-item {
            margin-bottom: 1.5rem;
        }

        .contact-item label {
            display: block;
            color: var(--color-text-light);
            font-size: 0.9rem;
            margin-bottom: 0.3rem;
            font-weight: bold;
        }

        .contact-item p {
            color: var(--color-text);
            font-size: 1rem;
        }

        .contact-item a {
            color: var(--color-primary);
            text-decoration: none;
            transition: color 0.3s;
        }

        .contact-item a:hover {
            color: #c69060;
        }

        .social-links {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .social-links a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: var(--color-primary);
            color: var(--color-dark);
            border-radius: 50%;
            text-decoration: none;
            font-size: 1.2rem;
            transition: all 0.3s;
        }

        .social-links a:hover {
            background: #c69060;
            transform: translateY(-2px);
        }

        .map-container {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            height: 400px;
        }

        @media (max-width: 768px) {
            .map-container {
                height: 300px;
            }
        }

        /* Footer */
        footer {
            background: var(--color-dark);
            color: white;
            text-align: center;
            padding: 2rem;
            margin-top: 2rem;
        }

        footer p {
            margin-bottom: 0.5rem;
            opacity: 0.8;
        }

        footer a {
            color: var(--color-primary);
            text-decoration: none;
        }

        footer a:hover {
            text-decoration: underline;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 1.8rem;
            }

            .section-title {
                font-size: 1.5rem;
            }

            .cta-section {
                padding: 1.5rem 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <!-- Header -->
        <header>
            <div class="logo">🏍 CuatriYesoTour</div>
            <nav>
                <a href="#tours">Tours</a>
                <a href="#gallery">Galería</a>
                <a href="#testimonials">Opiniones</a>
                <a href="#contact">Contacto</a>
            </nav>
        </header>

        <!-- Alert Banner -->
        <div class="alert-banner">
            <p>❄️ Tours de Verano Pausados</p>
            <p>Disponible ahora: <strong>"Camino al Embalse"</strong> | Termas reabre en Octubre</p>
        </div>

        <!-- Hero Section -->
        <section class="hero">
            <div class="hero-content">
                <h1>Aventura en cuatrimoto en el interior del cajón del Maipo</h1>
                <p>Vive la adrenalina en nuestros tours guiados por expertos. Camino al Embalse disponible ahora.</p>
                <a href="/reservas" class="btn-primary">Reservar Ahora</a>
            </div>
        </section>

        <!-- Tours Section -->
        <section class="tours-section" id="tours">
            <h2 class="section-title">Nuestros Tours</h2>
            <div class="tours-grid">
                <!-- Tour Camino al Embalse (DISPONIBLE AHORA) -->
                <div class="tour-card">
                    <div class="tour-badge">DISPONIBLE</div>
                    <div class="tour-image" style="background-image: url('https://cuatriyesotour.com/wp-content/uploads/2026/05/embalse-2.jpg');"></div>
                    <div class="tour-info">
                        <h3>Camino al Embalse</h3>
                        <div class="tour-times">
                            <div class="tour-time-slot"><strong>⏰ Mañana:</strong> 09:30 – 12:30</div>
                            <div class="tour-time-slot"><strong>⏰ Tarde:</strong> 14:30 – 17:00</div>
                        </div>
                        <p>Recorre el espectacular camino hacia el Embalse El Yeso. Un tour de aventura con vistas panorámicas impresionantes en un viaje de 2-3 horas de pura adrenalina.</p>
                        <ul class="tour-details">
                            <li>2-3 horas de duración</li>
                            <li>Vistas del Embalse El Yeso</li>
                            <li>Guía profesional</li>
                            <li>Máximo 8 personas (4 motos)</li>
                        </ul>
                        <div class="tour-price">$105.000 CLP</div>
                        <a href="/reservas" class="btn-book">Reservar</a>
                    </div>
                </div>

                <!-- Tour Termas (REABRE OCTUBRE) -->
                <div class="tour-card">
                    <div class="tour-badge coming-soon">REABRE EN OCTUBRE</div>
                    <div class="tour-image" style="background-image: url('https://cuatriyesotour.com/wp-content/uploads/2026/07/IMG_2488-scaled.jpg'); opacity: 0.6;"></div>
                    <div class="tour-info">
                        <h3>Tour Termas Valle de Colina</h3>
                        <div class="tour-times">
                            <div class="tour-time-slot"><strong>⏰ Disponible en:</strong> 09:30 – 14:30</div>
                        </div>
                        <p>Recorre los espectaculares Baños Morales y termas naturales en el corazón de la cordillera. Una experiencia única rodeado de montañas y naturaleza pura. <strong>Reapertura: Octubre 2026</strong></p>
                        <ul class="tour-details">
                            <li>5 horas de duración</li>
                            <li>Termas naturales incluidas</li>
                            <li>Guía profesional</li>
                            <li>Máximo 6 personas</li>
                        </ul>
                        <div class="tour-price">$185.000 CLP</div>
                        <button class="btn-book" disabled>Disponible en Octubre</button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Gallery Section -->
        <section class="gallery-section" id="gallery">
            <h2 class="section-title">Galería de Aventuras</h2>
            <div class="gallery-grid">
                <div class="gallery-item">
                    <img src="https://cuatriyesotour.com/wp-content/uploads/2026/05/embalse-2.jpg" alt="Camino al Embalse">
                </div>
                <div class="gallery-item">
                    <img src="https://cuatriyesotour.com/wp-content/uploads/2026/07/IMG_2488-scaled.jpg" alt="Paisaje">
                </div>
                <div class="gallery-item">
                    <img src="https://cuatriyesotour.com/wp-content/uploads/2026/05/embalse-2.jpg" alt="Cuatrimotos">
                </div>
                <div class="gallery-item">
                    <img src="https://cuatriyesotour.com/wp-content/uploads/2026/07/IMG_2488-scaled.jpg" alt="Montañas">
                </div>
                <div class="gallery-item">
                    <img src="https://cuatriyesotour.com/wp-content/uploads/2026/05/embalse-2.jpg" alt="Experiencia">
                </div>
                <div class="gallery-item">
                    <img src="https://cuatriyesotour.com/wp-content/uploads/2026/07/IMG_2488-scaled.jpg" alt="Aventura">
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <section class="testimonials-section" id="testimonials">
            <h2 class="section-title">¿Qué Dicen Nuestros Clientes?</h2>
            <div class="testimonials-grid">
                <div class="testimonial-card">
                    <div class="star-rating">★★★★★</div>
                    <p class="testimonial-text">"Una experiencia increíble. Los guías fueron profesionales y atentos. Las vistas del embalse son espectaculares. ¡Volveríamos sin dudarlo!"</p>
                    <div class="testimonial-author">- María González</div>
                </div>
                <div class="testimonial-card">
                    <div class="star-rating">★★★★★</div>
                    <p class="testimonial-text">"Tour perfecto para familias. Las vistas fueron impresionantes y el guía muy atento. Muy recomendado para pasar un día diferente."</p>
                    <div class="testimonial-author">- Carlos Rodríguez</div>
                </div>
                <div class="testimonial-card">
                    <div class="star-rating">★★★★★</div>
                    <p class="testimonial-text">"Excelente servicio de inicio a fin. El equipo de seguridad estaba impecable y el paisaje del Cajón del Maipo es simplemente hermoso. ¡Lo mejor!"</p>
                    <div class="testimonial-author">- Ana Martínez</div>
                </div>
            </div>
        </section>

        <!-- Stats Section -->
        <section class="stats-section">
            <h2 class="section-title">Por Qué Elegirnos</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number">250+</div>
                    <div class="stat-label">Tours Completados</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">5.0</div>
                    <div class="stat-label">Calificación Promedio</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">98%</div>
                    <div class="stat-label">Clientes Satisfechos</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">8+</div>
                    <div class="stat-label">Años de Experiencia</div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="cta-section" id="calendar">
            <h2>Reserva Tu Aventura Hoy</h2>
            <p>Elige tu fecha preferida y comienza la experiencia de tu vida</p>
            <a href="/reservas" class="btn-primary">Ir al Calendario de Reservas →</a>
        </section>

        <!-- Inclusions Section -->
        <section class="inclusions-section" id="inclusions">
            <h2 class="section-title">¿Qué Incluye?</h2>
            <div class="inclusions-grid">
                <div class="inclusion-card">
                    <div class="inclusion-icon">🏍️</div>
                    <h4>Cuatrimoto Incluida</h4>
                    <p>Cuatrimotos de última generación, mantenidas y seguras para disfrutar plenamente.</p>
                </div>
                <div class="inclusion-card">
                    <div class="inclusion-icon">👨‍🏫</div>
                    <h4>Guía Profesional</h4>
                    <p>Guías certificados y experimentados que conocen cada ruta a la perfección.</p>
                </div>
                <div class="inclusion-card">
                    <div class="inclusion-icon">🛡️</div>
                    <h4>Equipo de Seguridad</h4>
                    <p>Cascos, protecciones y equipamiento completo para máxima seguridad.</p>
                </div>
                <div class="inclusion-card">
                    <div class="inclusion-icon">🌿</div>
                    <h4>Experiencia Natural</h4>
                    <p>Explora paisajes impresionantes del Cajón del Maipo en un ambiente natural único.</p>
                </div>
            </div>
        </section>

        <!-- Restrictions Section -->
        <section class="restrictions-section">
            <div class="restrictions-box">
                <h3>⚠️ Restricciones y Consideraciones Importantes</h3>
                <ul>
                    <li><strong>Edad mínima:</strong> 16 años (con supervisión de adulto menores a 18)</li>
                    <li><strong>Licencia requerida:</strong> Licencia de conducir válida</li>
                    <li><strong>Experiencia previa:</strong> No se requiere, pero recomendamos experiencia básica</li>
                    <li><strong>Condiciones físicas:</strong> Buena condición física y sin limitaciones de movilidad</li>
                    <li><strong>Clima:</strong> Tours pueden ser cancelados por lluvia o condiciones climáticas adversas</li>
                    <li><strong>Cancelación:</strong> Reembolso total si se cancela con 24 horas de anticipación</li>
                </ul>
            </div>
        </section>

        <!-- FAQ Section -->
        <section class="faq-section">
            <h2 class="section-title">Preguntas Frecuentes</h2>
            <div class="faq-container">
                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFaq(this)">
                        <h4>¿Cuándo reabre el tour Termas?</h4>
                        <span class="faq-toggle">+</span>
                    </div>
                    <div class="faq-answer">
                        <p>El tour Termas Valle de Colina reabre en octubre cuando mejoren las condiciones del camino y del clima. Por ahora, te ofrecemos el tour "Camino al Embalse" disponible todos los días con dos horarios.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFaq(this)">
                        <h4>¿Necesito experiencia previa en cuatrimoto?</h4>
                        <span class="faq-toggle">+</span>
                    </div>
                    <div class="faq-answer">
                        <p>No, no es necesario tener experiencia previa. Nuestros guías profesionales te enseñarán cómo operar la cuatrimoto de manera segura. Recomendamos tener al menos una condición física básica para disfrutar plenamente de la experiencia.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFaq(this)">
                        <h4>¿Cuál es la edad mínima para participar?</h4>
                        <span class="faq-toggle">+</span>
                    </div>
                    <div class="faq-answer">
                        <p>La edad mínima es 16 años. Los menores de 18 años deben estar acompañados por un adulto responsable durante todo el tour. Es necesario presentar un documento de identidad válido.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFaq(this)">
                        <h4>¿Qué debo llevar conmigo?</h4>
                        <span class="faq-toggle">+</span>
                    </div>
                    <div class="faq-answer">
                        <p>Te recomendamos llevar: protector solar, repelente de insectos, ropa cómoda y cerrada, zapatos deportivos seguros, una muda de ropa si lo deseas, toalla, y una bolsa impermeable para tus objetos de valor. Nosotros proporcionamos todo el equipo de seguridad.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFaq(this)">
                        <h4>¿Cuál es la política de cancelación?</h4>
                        <span class="faq-toggle">+</span>
                    </div>
                    <div class="faq-answer">
                        <p>Ofrecemos reembolso completo si cancelas con al menos 24 horas de anticipación. Cancelaciones con menos de 24 horas pueden incurrir en cargos. En caso de mal tiempo, reasignaremos tu reserva a otra fecha sin costo adicional.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFaq(this)">
                        <h4>¿Cuáles son los métodos de pago?</h4>
                        <span class="faq-toggle">+</span>
                    </div>
                    <div class="faq-answer">
                        <p>Aceptamos tarjetas de crédito y débito nacionales e internacionales a través de nuestra plataforma segura de pagos. También puedes consultar sobre otras opciones de pago contactándonos directamente.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Contact Section -->
        <section class="contact-section" id="contact">
            <h2 class="section-title">Contacto</h2>
            <div class="contact-container">
                <div class="contact-info">
                    <h3>Información de Contacto</h3>
                    <div class="contact-item">
                        <label>Correo Electrónico</label>
                        <p><a href="mailto:cuatriyesotour@gmail.com">cuatriyesotour@gmail.com</a></p>
                    </div>
                    <div class="contact-item">
                        <label>Ubicación</label>
                        <p>Cajón del Maipo, Región Metropolitana, Chile</p>
                    </div>
                    <div class="contact-item">
                        <label>Síguenos en Redes Sociales</label>
                        <div class="social-links">
                            <a href="https://instagram.com/cuatriyesotour" title="Instagram" target="_blank">📷</a>
                            <a href="https://tiktok.com/@cuatriyesotour" title="TikTok" target="_blank">🎵</a>
                        </div>
                    </div>
                    <div class="contact-item">
                        <label>Horarios de Atención</label>
                        <p>Lunes - Domingo: 08:00 - 18:00<br>Consultanos por disponibilidad especial</p>
                    </div>
                </div>
                <div class="map-container">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3353.4186887584387!2d-70.28427!3d-33.65228!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x9662e1ea0dd0d1bd%3A0x8400a8fa6f54130!2sCaj%C3%B3n%20del%20Maipo%2C%20Metropolitana!5e0!3m2!1ses!2scl!4v1234567890" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer>
            <p>&copy; 2026 CuatriYesoTour. Todos los derechos reservados.</p>
            <p>Aventuras en cuatrimoto en el Cajón del Maipo | Camino al Embalse</p>
            <p><a href="/privacy-policy">Política de Privacidad</a> | <a href="/terms">Términos y Condiciones</a></p>
        </footer>
    </div>

    <script>
        function toggleFaq(element) {
            const faqItem = element.parentElement;
            faqItem.classList.toggle('active');
        }
    </script>
</body>
</html>
