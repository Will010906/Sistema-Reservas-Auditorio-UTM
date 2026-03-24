<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>SIRA</title>
        <!-- Favicon-->
        <link rel="icon" type="image/x-icon" href="assets/favicon.ico">
        <!-- Font Awesome icons (free version)-->
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
        <!-- Google fonts-->
        <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700" rel="stylesheet" type="text/css" />
        <link href="https://fonts.googleapis.com/css?family=Roboto+Slab:400,100,300,700" rel="stylesheet" type="text/css" />
        <!-- estilo de la pagina-->
        <link href="assets/css/pagina.css?v=2.0" rel="stylesheet">
    </head>
    <body id="page-top">
        <!-- Navigation-->
        <nav class="navbar navbar-expand-lg navbar-dark fixed-top" id="mainNav">
            <div class="container">
                <!-- ESTO ES SI TENEMOS LOGO EN LETRAS QUE SE VEA BIEN 
                <a class="navbar-brand" href="#page-top"><img src="assets/img/logos/logoSIRA.jpeg" alt="..." /></a>-->
                <a class="navbar-brand" href="#page-top">SIRA-UTM</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
                    Menu
                    <i class="fas fa-bars ms-1"></i>
                </button>
                <div class="collapse navbar-collapse" id="navbarResponsive">
                    <ul class="navbar-nav text-uppercase ms-auto py-4 py-lg-0">
                        <li class="nav-item"><a class="nav-link" href="#servicios">Servicios</a></li>
                        <li class="nav-item"><a class="nav-link" href="#catalogo">Catálogo</a></li>
                        <li class="nav-item"><a class="nav-link" href="#guia">Guía</a></li>
                        <li class="nav-item"><a class="nav-link" href="#equipo">Equipo</a></li>
                        <li class="nav-item"><a class="nav-link" href="#contacto">Contacto</a></li>
                        <li class="nav-item">
                            <a class="nav-link btn-login-nav" href="login.php">Iniciar Sesión</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        <!-- Masthead-->
        <header class="masthead">
            <div class="container">
                <div class="masthead-subheading">¡BIENVENIDO A SIRA!</div>
                <div class="masthead-heading text-uppercase">Tu evento empieza aquí</div>
                <a class="btn btn-primary btn-xl text-uppercase" href="#servicios">Leer más</a>
            </div>
        </header>
        <!-- Servicios-->
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
        <!-- catalogo Grid-->
        <section class="page-section bg-light" id="catalogo">
            <div class="container">
                <div class="text-center">
                    <h2 class="section-heading text-uppercase">Catálogo de Espacios</h2>
                    <h3 class="section-subheading text-muted">Descubre el espacio perfecto para tu próximo foro, conferencia o taller.<br> 
                        En nuestro <strong>Catálogo de Espacios</strong> encontrarás auditorios con tecnología de punta y la capacidad exacta que tu proyecto requiere.</h3>
                </div>
                <div class="row">
                    <div class="col-lg-4 col-sm-6 mb-4">
                        <!-- catalogo item 1-->
                        <div class="catalogo-item">
                            <a class="catalogo-link" data-bs-toggle="modal" href="#catalogoModal1">
                                <div class="catalogo-hover">
                                    <div class="catalogo-hover-content"><i class="fas fa-plus fa-3x"></i></div>
                                </div>
                                <img class="img-fluid" src="assets/img/portfolio/11.jpg" alt="..." />
                            </a>
                            <div class="catalogo-caption">
                                <div class="catalogo-caption-heading">Auditorio Principal</div>
                                <div class="catalogo-caption-subheading text-muted">
                                    Capacidad: 150 personas<br>
                                    Ubicación: Edificio A
                            </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-sm-6 mb-4">
                        <!-- catalogo item 2-->
                        <div class="catalogo-item">
                            <a class="catalogo-link" data-bs-toggle="modal" href="#catalogoModal2">
                                <div class="catalogo-hover">
                                    <div class="catalogo-hover-content"><i class="fas fa-plus fa-3x"></i></div>
                                </div>
                                <img class="img-fluid" src="assets/img/portfolio/22.jpg" alt="..." />
                            </a>
                            <div class="catalogo-caption">
                                <div class="catalogo-caption-heading">Auditorio B</div>
                                <div class="catalogo-caption-subheading text-muted">
                                    Capacidad: 100 personas<br>
                                    Ubicación: Edificio B
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-sm-6 mb-4">
                        <!-- catalogo item 3-->
                        <div class="catalogo-item">
                            <a class="catalogo-link" data-bs-toggle="modal" href="#catalogoModal3">
                                <div class="catalogo-hover">
                                    <div class="catalogo-hover-content"><i class="fas fa-plus fa-3x"></i></div>
                                </div>
                                <img class="img-fluid" src="assets/img/portfolio/33.jpg" alt="..." />
                            </a>
                            <div class="catalogo-caption">
                                <div class="catalogo-caption-heading">Auditorio A</div>
                                <div class="catalogo-caption-subheading text-muted">
                                    Capacidad: 75 personas<br>
                                    Ubicación: Edificio A
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-sm-6 mb-4 mb-lg-0">
                        <!-- catalogo item 4-->
                        <div class="catalogo-item">
                            <a class="catalogo-link" data-bs-toggle="modal" href="#catalogoModal4">
                                <div class="catalogo-hover">
                                    <div class="catalogo-hover-content"><i class="fas fa-plus fa-3x"></i></div>
                                </div>
                                <img class="img-fluid" src="assets/img/portfolio/44.jpg" alt="..." />
                            </a>
                            <div class="catalogo-caption">
                                <div class="catalogo-caption-heading">Auditorio C</div>
                                <div class="catalogo-caption-subheading text-muted">
                                    Capacidad: 120 personas<br>
                                    Ubicación: Edificio C
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-sm-6 mb-4 mb-sm-0">
                        <!-- catalogo item 5-->
                        <div class="catalogo-item">
                            <a class="catalogo-link" data-bs-toggle="modal" href="#catalogoModal5">
                                <div class="catalogo-hover">
                                    <div class="catalogo-hover-content"><i class="fas fa-plus fa-3x"></i></div>
                                </div>
                                <img class="img-fluid" src="assets/img/portfolio/55.jpg" alt="..." />
                            </a>
                            <div class="catalogo-caption">
                                <div class="catalogo-caption-heading">Auditorio A-2</div>
                                <div class="catalogo-caption-subheading text-muted">
                                    Capacidad: 50 personas<br>
                                    Ubicación: Edificio A
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-sm-6">
                        <!-- catalogo item 6-->
                        <div class="catalogo-item">
                            <a class="catalogo-link" data-bs-toggle="modal" href="#catalogoModal6">
                                <div class="catalogo-hover">
                                    <div class="catalogo-hover-content"><i class="fas fa-plus fa-3x"></i></div>
                                </div>
                                <img class="img-fluid" src="assets/img/portfolio/66.jpg" alt="..." />
                            </a>
                            <div class="catalogo-caption">
                                <div class="catalogo-caption-heading">Auditorio D</div>
                                <div class="catalogo-caption-subheading text-muted">
                                    Capacidad: 150 personas<br>
                                    Ubicación: Edificio D
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- guia-->
        <section class="page-section" id="guia">
            <div class="container">
                <div class="text-center">
                    <h2 class="section-heading text-uppercase">Guía de Reserva</h2>
                    <h3 class="section-subheading text-muted">Conoce como funciona SIRA</h3>
                </div>
                <ul class="timeline">
                    <li>
                        <div class="timeline-image"><img class="rounded-circle img-fluid" src="assets/img/about/1.jpg" alt="..." /></div>
                        <div class="timeline-panel">
                            <div class="timeline-heading">
                                <h4>Paso 1:</h4>
                                <h4 class="subheading">Acceso Institucional</h4>
                            </div>
                            <div class="timeline-body"><p class="text-muted">Ingresa con tus credenciales de la UTM para validar tu identidad académica.</p></div>
                        </div>
                    </li>
                    <li class="timeline-inverted">
                        <div class="timeline-image"><img class="rounded-circle img-fluid" src="assets/img/about/2.jpg" alt="..." /></div>
                        <div class="timeline-panel">
                            <div class="timeline-heading">
                                <h4>Paso 2:</h4>
                                <h4 class="subheading">Selección de Espacio</h4>
                            </div>
                            <div class="timeline-body"><p class="text-muted">Explora los auditorios disponibles y verifica la disponibilidad en el calendario en tiempo real.</p></div>
                        </div>
                    </li>
                    <li>
                        <div class="timeline-image"><img class="rounded-circle img-fluid" src="assets/img/about/3.jpg" alt="..." /></div>
                        <div class="timeline-panel">
                            <div class="timeline-heading">
                                <h4>Paso 3:</h4>
                                <h4 class="subheading">Validación de Solicitud</h4>
                            </div>
                            <div class="timeline-body"><p class="text-muted">El equipo administrativo revisará tu petición y recibirás una notificación de confirmación.</p></div>
                        </div>
                    </li>
                    <li class="timeline-inverted">
                        <div class="timeline-image"><img class="rounded-circle img-fluid" src="assets/img/about/4.jpg" alt="..." /></div>
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
                            <h4>
                                ¡TU EVENTO
                                <br />
                                EMPIEZA
                                <br />
                                AQUÍ!
                            </h4>
                        </div>
                    </li>
                </ul>
            </div>
        </section>
        <!-- equipo-->
        <section class="page-section bg-light" id="equipo">
    <div class="container">
        <div class="text-center">
            <h2 class="section-heading text-uppercase">Equipo Responsable</h2>
            <h3 class="section-subheading text-muted">Administración / Contacto Directo</h3>
        </div>
        <div class="row">
            <div class="col-lg-4">
                <div class="equipo-member text-center">
                    <span class="fa-stack fa-4x">
                        <i class="fas fa-circle fa-stack-2x text-primary"></i>
                        <i class="fas fa-users fa-stack-1x fa-inverse"></i>
                    </span>
                    <h4>Coordinación de Eventos</h4>
                    <p class="text-muted">Gestión y autorización de cronogramas institucionales.</p>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="equipo-member text-center">
                    <span class="fa-stack fa-4x">
                        <i class="fas fa-circle fa-stack-2x text-primary"></i>
                        <i class="fas fa-headset fa-stack-1x fa-inverse"></i>
                    </span>
                    <h4>Soporte Técnico</h4>
                    <p class="text-muted">Supervisión de sistemas audiovisuales y logística técnica.</p>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="equipo-member text-center">
                    <span class="fa-stack fa-4x">
                        <i class="fas fa-circle fa-stack-2x text-primary"></i>
                        <i class="fas fa-wrench fa-stack-1x fa-inverse"></i>
                    </span>
                    <h4>Mantenimiento</h4>
                    <p class="text-muted">Aseguramiento de la infraestructura y condiciones del espacio.</p>
                </div>
            </div>
        </div>
    </div>
</section>
        
        <!-- Contacto-->
        <section class="page-section" id="contacto">
            <div class="container">
                <div class="text-center">
                    <h2 class="section-heading text-uppercase">Contactanos</h2>
                    <h3 class="section-subheading text-muted">
                        <strong>Ubicación: </strong>Edificio A, Planta Baja.<br>
                        <strong>Horarios: </strong>Lunes a Viernes de 9:00 AM a 3:00 PM
                    </h3>
                </div>
                <!-- to get an API token!-->
                <form id="contactoForm" data-sb-form-api-token="API_TOKEN">
                    <div class="row align-items-stretch mb-5">
                        <div class="col-md-6">
                            <div class="form-group">
                                <!-- Name input-->
                                <input class="form-control" id="name" type="text" placeholder="Tu Nombre *" data-sb-validations="required" />
                                <div class="invalid-feedback" data-sb-feedback="name:required">Nombre requerido</div>
                            </div>
                            <div class="form-group">
                                <!-- Email address input-->
                                <input class="form-control" id="email" type="email" placeholder="Tu Email *" data-sb-validations="required,email" />
                                <div class="invalid-feedback" data-sb-feedback="email:required">Email requerido</div>
                                <div class="invalid-feedback" data-sb-feedback="email:email">Email no valido</div>
                            </div>
                            <div class="form-group mb-md-0">
                                <!-- Phone number input-->
                                <input class="form-control" id="phone" type="tel" placeholder="Tu Número *" data-sb-validations="required" />
                                <div class="invalid-feedback" data-sb-feedback="phone:required">Número requerido</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group form-group-textarea mb-md-0">
                                <!-- Message input-->
                                <textarea class="form-control" id="message" placeholder="Tus Dudas *" data-sb-validations="required"></textarea>
                                <div class="invalid-feedback" data-sb-feedback="message:required">El mensaje es requerido</div>
                            </div>
                        </div>
                    </div>
                    <!-- Submit success message-->
                    <!---->
                    <!-- This is what your users will see when the form-->
                    <!-- has successfully submitted-->
                    <div class="d-none" id="submitSuccessMessage">
                        <div class="text-center text-white mb-3">
                            <div class="fw-bolder">¡Envío de Formulario Exitoso!</div>
                            Ingresa a 
                            <br />
                            <a href="https://startbootstrap.com/solution/contacto-forms">https://startbootstrap.com/solution/contacto-forms</a>
                        </div>
                    </div>
                    <!-- Submit error message-->
                    <!---->
                    <!-- This is what your users will see when there is-->
                    <!-- an error submitting the form-->
                    <div class="d-none" id="submitErrorMessage"><div class="text-center text-danger mb-3">Error de envío</div></div>
                    <!-- Submit Button-->
                    <div class="text-center"><button class="btn btn-primary btn-xl text-uppercase disabled" id="submitButton" type="submit">Enviar Mensaje</button></div>
                </form>
            </div>
        </section>
        <!-- Footer-->
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
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Bootstrap core JS-->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
        <!-- Core theme JS-->
        <script src="assets/js/pagina.js"></script>
        <script src="https://cdn.startbootstrap.com/sb-forms-latest.js"></script>
    </body>
</html>
