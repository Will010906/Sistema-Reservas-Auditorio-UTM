<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="SIRA - Sistema Integral de Reserva de Auditorios UTM" />
        <meta name="author" content="SIRA UTM" />
        <title>SIRA - UTM</title>
        <?php include 'includes/head.php'; ?>
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
        
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
        
        <link href="assets/css/pagina.css?v=4.0" rel="stylesheet">
    </head>
    <body id="page-top">
        <nav class="navbar navbar-expand-lg navbar-dark fixed-top" id="mainNav">
            <div class="container">
                <a class="navbar-brand d-flex align-items-center" href="#page-top">
                    <img src="assets/img/logo_app_web_RA_SB.png" alt="SIRA Logo" style="height: 50px; width: auto; margin-right: 12px;">
                    <span class="fw-bold" style="letter-spacing: 1px;">SIRA-UTM</span>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
                    Menu
                    <i class="fas fa-bars ms-1"></i>
                </button>
                <div class="collapse navbar-collapse" id="navbarResponsive">
                    <ul class="navbar-nav text-uppercase ms-auto py-4 py-lg-0">
                        <li class="nav-item"><a class="nav-link" href="#servicios">Servicios</a></li>
                        <li class="nav-item"><a class="nav-link" href="#catalogo">Catálogo</a></li>
                        <li class="nav-item"><a class="nav-link" href="#guia">Guía</a></li>
                        <li class="nav-item"><a class="nav-link" href="#registro">Regístrate</a></li>
                        <li class="nav-item"><a class="nav-link" href="#contacto">Contacto</a></li>
                        <li class="nav-item">
                            <a class="nav-link btn-login-nav" href="login.php">Iniciar Sesión</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <header class="masthead">
            <div class="container">
                <div class="masthead-subheading">¡BIENVENIDO A SIRA!</div>
                <div class="masthead-heading text-uppercase">Tu evento empieza aquí</div>
                <a class="btn btn-primary btn-xl text-uppercase" href="#servicios">Leer más</a>
            </div>
        </header>

        <section class="page-section" id="servicios">
            <div class="container">
                <div class="text-center">
                    <h2 class="section-heading text-uppercase">Servicios</h2>
                    <h3 class="section-subheading text-muted">La plataforma integral para el control y reserva de auditorios en UTM.</h3>
                </div>
                <div class="row text-center">
                    <div class="col-md-4">
                        <span class="fa-stack fa-4x">
                            <i class="fas fa-circle fa-stack-2x text-primary"></i>
                            <i class="fas fa-calendar-check fa-stack-1x fa-inverse"></i>
                        </span>
                        <h4 class="my-3">Reserva en Línea</h4>
                        <p class="text-muted">Consulta la disponibilidad en tiempo real y aparta tu fecha desde cualquier lugar.</p>
                    </div>
                    <div class="col-md-4">
                        <span class="fa-stack fa-4x">
                            <i class="fas fa-circle fa-stack-2x text-primary"></i>
                            <i class="fas fa-tasks fa-stack-1x fa-inverse"></i>
                        </span>
                        <h4 class="my-3">Gestión de Eventos</h4>
                        <p class="text-muted">Sube los detalles de tu evento (nombre, requerimientos técnicos).</p>
                    </div>
                    <div class="col-md-4">
                        <span class="fa-stack fa-4x">
                            <i class="fas fa-circle fa-stack-2x text-primary"></i>
                            <i class="fas fa-microchip fa-stack-1x fa-inverse"></i>
                        </span>
                        <h4 class="my-3">Soporte Técnico</h4>
                        <p class="text-muted">Solicita equipo adicional como proyectores, micrófonos o iluminación especial.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="page-section bg-light" id="catalogo">
            <div class="container">
                <div class="text-center">
                    <h2 class="section-heading text-uppercase">Catálogo de Espacios</h2>
                    <h3 class="section-subheading text-muted">Descubre el espacio perfecto para tu próximo foro, conferencia o taller.</h3>
                </div>
                <div class="row">
                    <div class="col-lg-6 col-sm-6 mb-4">
                        <div class="catalogo-item">
                            <a class="catalogo-link" data-bs-toggle="modal" href="#catalogoModal1">
                                <div class="catalogo-hover">
                                    <div class="catalogo-hover-content"><i class="fas fa-plus fa-3x"></i></div>
                                </div>
                                <img class="img-fluid" src="assets/img/catalogo/1D.jpeg" alt="Auditorio D" />
                            </a>
                            <div class="catalogo-caption">
                                <div class="catalogo-caption-heading">Auditorio D</div>
                                <div class="catalogo-caption-subheading text-muted">Capacidad: 182 personas | Ubicación: Edificio D</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-sm-6 mb-4">
                        <div class="catalogo-item">
                            <a class="catalogo-link" data-bs-toggle="modal" href="#catalogoModal2">
                                <div class="catalogo-hover">
                                    <div class="catalogo-hover-content"><i class="fas fa-plus fa-3x"></i></div>
                                </div>
                                <img class="img-fluid" src="assets/img/catalogo/2B.jpeg" alt="Auditorio B" />
                            </a>
                            <div class="catalogo-caption">
                                <div class="catalogo-caption-heading">Auditorio B</div>
                                <div class="catalogo-caption-subheading text-muted">Capacidad: 80 personas | Ubicación: Edificio B</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-sm-6 mb-4">
                        <div class="catalogo-item">
                            <a class="catalogo-link" data-bs-toggle="modal" href="#catalogoModal3">
                                <div class="catalogo-hover">
                                    <div class="catalogo-hover-content"><i class="fas fa-plus fa-3x"></i></div>
                                </div>
                                <img class="img-fluid" src="assets/img/catalogo/1A.jpeg" alt="Auditorio A-2" />
                            </a>
                            <div class="catalogo-caption">
                                <div class="catalogo-caption-heading">Auditorio A-2</div>
                                <div class="catalogo-caption-subheading text-muted">Capacidad: 63 personas | Ubicación: Edificio A (PA)</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-sm-6 mb-4">
                        <div class="catalogo-item">
                            <a class="catalogo-link" data-bs-toggle="modal" href="#catalogoModal4">
                                <div class="catalogo-hover">
                                    <div class="catalogo-hover-content"><i class="fas fa-plus fa-3x"></i></div>
                                </div>
                                <img class="img-fluid" src="assets/img/catalogo/2-A.jpeg" alt="Auditorio A" />
                            </a>
                            <div class="catalogo-caption">
                                <div class="catalogo-caption-heading">Auditorio A</div>
                                <div class="catalogo-caption-subheading text-muted">Capacidad: 65 personas | Ubicación: Edificio A (PB)</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="page-section" id="guia">
            <div class="container">
                <div class="text-center">
                    <h2 class="section-heading text-uppercase">Guía de Reserva</h2>
                    <h3 class="section-subheading text-muted">Conoce cómo funciona SIRA</h3>
                </div>
                <ul class="timeline">
                    <li>
                        <div class="timeline-image"><img class="rounded-circle img-fluid" src="assets/img/guia/inicio.jpeg" alt="Paso 1" /></div>
                        <div class="timeline-panel">
                            <div class="timeline-heading">
                                <h4>Paso 1:</h4>
                                <h4 class="subheading">Acceso Institucional</h4>
                            </div>
                            <div class="timeline-body"><p class="text-muted">Ingresa con tus credenciales de la UTM para validar tu identidad académica.</p></div>
                        </div>
                    </li>
                    <li class="timeline-inverted">
                        <div class="timeline-image"><img class="rounded-circle img-fluid" src="assets/img/guia/solicitud.jpeg" alt="Paso 2" /></div>
                        <div class="timeline-panel">
                            <div class="timeline-heading">
                                <h4>Paso 2:</h4>
                                <h4 class="subheading">Selección de Espacio</h4>
                            </div>
                            <div class="timeline-body"><p class="text-muted">Explora los auditorios disponibles y verifica la disponibilidad en tiempo real.</p></div>
                        </div>
                    </li>
                    <li>
                        <div class="timeline-image"><img class="rounded-circle img-fluid" src="assets/img/guia/revision.jpeg" alt="Paso 3" /></div>
                        <div class="timeline-panel">
                            <div class="timeline-heading">
                                <h4>Paso 3:</h4>
                                <h4 class="subheading">Validación de Solicitud</h4>
                            </div>
                            <div class="timeline-body"><p class="text-muted">El equipo administrativo revisará tu petición y recibirás una notificación.</p></div>
                        </div>
                    </li>
                    <li class="timeline-inverted">
                        <div class="timeline-image"><img class="rounded-circle img-fluid" src="assets/img/guia/UTM.jpeg" alt="Paso 4" /></div>
                        <div class="timeline-panel">
                            <div class="timeline-heading">
                                <h4>Paso 4:</h4>
                                <h4 class="subheading">Realización del Evento</h4>
                            </div>
                            <div class="timeline-body"><p class="text-muted">Coordina con soporte técnico y lleva a cabo tu actividad en la fecha programada.</p></div>
                        </div>
                    </li>
                    <li class="timeline-inverted">
                        <div class="timeline-image">
                            <h4>¡TU EVENTO<br />EMPIEZA<br />AQUÍ!</h4>
                        </div>
                    </li>
                </ul>
            </div>
        </section>

        <section class="page-section bg-light" id="registro">
            <div class="container">
                <div class="text-center">
                    <h2 class="section-heading text-uppercase">¿No formas parte de la comunidad UTM?</h2>
                    <h3 class="section-subheading text-muted">Registra tu cuenta externa para solicitar acceso a nuestros espacios.</h3>
                </div>
                <div class="row text-center">
                    <div class="col-lg-4">
                        <div class="registro-member">
                            <span class="fa-stack fa-4x">
                                <i class="fas fa-circle fa-stack-2x text-primary"></i>
                                <i class="fas fa-user-plus fa-stack-1x fa-inverse"></i> 
                            </span>
                            <h4><strong>Crea tu Perfil</strong></h4>
                            <p class="text-muted">Proporciona tus datos básicos y de contacto.</p>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="registro-member">
                            <span class="fa-stack fa-4x">
                                <i class="fas fa-circle fa-stack-2x text-primary"></i>
                                <i class="fas fa-id-card fa-stack-1x fa-inverse"></i> 
                            </span>
                            <h4><strong>Verificación</strong></h4>
                            <p class="text-muted">Nuestro equipo validará tu identidad para permitirte realizar solicitudes.</p>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="registro-member">
                            <span class="fa-stack fa-4x">
                                <i class="fas fa-circle fa-stack-2x text-primary"></i>
                                <i class="fas fa-calendar-check fa-stack-1x fa-inverse"></i> 
                            </span>
                            <h4><strong>Reserva</strong></h4>
                            <p class="text-muted">Una vez aprobado, podrás gestionar tus eventos eficientemente.</p>
                        </div>
                    </div>
                </div>
                <div class="text-center mt-5">
                    <p class="large text-muted">¿Listo para comenzar?</p>
                    <a class="btn btn-primary btn-xl text-uppercase" href="registro.php"><strong>Crear Cuenta de Visitante</strong></a>
                </div>
            </div>
        </section>

        <section class="page-section" id="contacto">
            <div class="container">
                <div class="text-center">
                    <h2 class="section-heading text-uppercase">Contáctanos</h2>
                    <h3 class="section-subheading text-white">Edificio A, Planta Baja | Lunes a Viernes de 9:00 AM a 3:00 PM</h3>
                </div>
                <form id="contactoForm" action="enviar_correo.php" method="POST">
                    <div class="row align-items-stretch mb-5">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <input class="form-control" id="nombre" name="nombre" type="text" placeholder="Tu Nombre *" required />
                            </div>
                            <div class="form-group mb-3">
                                <input class="form-control" id="email" name="email" type="email" placeholder="Tu Correo *" required />
                            </div>
                            <div class="form-group mb-md-0">
                                <input class="form-control" id="phone" name="phone" type="tel" placeholder="Tu Teléfono *" required />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group form-group-textarea mb-md-0">
                                <textarea class="form-control" id="message" name="message" placeholder="Tus Dudas *" required style="min-height: 150px;"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="text-center">
                        <button class="btn btn-primary btn-xl text-uppercase" type="submit">Enviar Mensaje</button>
                    </div>
                </form>
            </div>
        </section>

        <footer class="footer py-4">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-4 text-lg-start">Copyright &copy; Universidad Tecnológica de Morelia 2026</div>
                    <div class="col-lg-4 my-3 my-lg-0">
                        <a class="btn btn-dark btn-social mx-2" href="#!" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                        <a class="btn btn-dark btn-social mx-2" href="#!" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a class="btn btn-dark btn-social mx-2" href="#!" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                    <div class="col-lg-4 text-lg-end">
                        <a class="link-dark text-decoration-none me-3" href="#!">Privacy Policy</a>
                        <a class="link-dark text-decoration-none" href="#!">Terms of Use</a>
                    </div>
                </div>
            </div>
        </footer>

        <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>

<script src="https://cdn.startbootstrap.com/sb-forms-latest.js"></script>

<script src="assets/js/pagina.js"></script>
    </body>
</html>