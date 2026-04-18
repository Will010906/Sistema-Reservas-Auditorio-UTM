<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="SIRA - Sistema Integral de Reserva de Auditorios UTM" />
        <meta name="author" content="SIRA UTM" />
        <title>SIRA</title>
        <?php include 'includes/head.php'; ?>
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
        
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
        
        <link href="assets/css/pagina.css?v=4.0" rel="stylesheet">
    </head>
    <body id="page-top">
       <nav class="navbar navbar-expand-lg navbar-dark fixed-top" id="mainNav">
    <div class="container-fluid px-lg-5"> <a class="navbar-brand d-flex align-items-center" href="#page-top">
            <img src="assets/img/logos/logo_utm.png" alt="UTM" style="height: 50px; margin-right: 12px;">
            <img src="assets/img/logos/logo_app_web_RA_SB.png" alt="SIRA" style="height: 50px;">
            <span class="ms-2 fw-bold border-start ps-3 d-none d-xl-inline" style="letter-spacing: 1px;">SIRA</span>
        </a>

        <div class="collapse navbar-collapse" id="navbarResponsive">
            <ul class="navbar-nav text-uppercase mx-auto py-4 py-lg-0">
                <li class="nav-item"><a class="nav-link" href="#servicios">Servicios</a></li>
                <li class="nav-item"><a class="nav-link" href="#propuesta">Propuesta</a></li>
                <li class="nav-item"><a class="nav-link" href="#roles">Roles</a></li>
                
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                        Proyecto
                    </a>
                    <ul class="dropdown-menu shadow border-0">
                        <li><a class="dropdown-item" href="#tecnologia">Tecnología</a></li>
                        <li><a class="dropdown-item" href="#equipo">Equipo</a></li>
                        <li><a class="dropdown-item" href="#catalogo">Catálogo</a></li>
                        <li><a class="dropdown-item" href="#guia">Guía</a></li>
                    </ul>
                </li>
                
                <li class="nav-item"><a class="nav-link" href="#contacto">Contacto</a></li>
            </ul>

            <div class="d-flex align-items-center gap-2">
                <a class="btn btn-outline-light btn-sm text-uppercase fw-bold" href="#registro">Regístrate</a>
                <a class="btn btn-primary btn-sm text-uppercase fw-bold px-3" href="login.php" style="background-color: #5B3D66; border: none;">Iniciar Sesión</a>
            </div>
        </div>
    </div>
</nav>

     <header class="masthead">
    <div class="hero-container text-center px-4">
        <p class="text-uppercase fw-bold mb-3" style="letter-spacing: 4px; color: #D4ADFC;">
            ¡Bienvenidos a SIRA!
        </p>
        
        <h1 class="display-3 fw-bold text-white mb-4" style="text-shadow: 2px 4px 10px rgba(0,0,0,0.6); line-height: 1.1;">
            SISTEMA INTEGRAL DE <br>
            RESERVACIÓN DE AUDITORIOS
        </h1>
        
        <p class="lead mb-5 text-white fw-bold" style="text-shadow: 1px 1px 5px rgba(0,0,0,0.8); max-width: 800px; margin: 0 auto;">
            Gestiona tus espacios académicos de forma rápida, segura y eficiente.
        </p>

       <a href="#servicios" class="btn px-5 py-3 rounded-pill text-uppercase fw-bold shadow-lg" 
   style="background-color: #5B3D66; color: white !important; border: 2px solid rgba(255,255,255,0.2); min-width: 280px; font-size: 1.1rem; transition: all 0.3s ease;">
    Explorar Servicios <i class="bi bi-chevron-down ms-2"></i>
</a>
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

          <section class="page-section" id="propuesta">
            <div class="container">
                <div class="text-center">
                    <h2 class="section-heading text-uppercase">Propuesta de Solución</h2>
                    <h3 class="section-subheading text-muted">¿Por qué usar SIRA?</h3>
                </div>
                <div class="row text-center">
                    <div class="col-md-6">
                        <span class="fa-stack fa-4x">
                            <i class="fas fa-circle fa-stack-2x text-danger"></i>
                            <i class="fas fa-times-circle fa-stack-1x fa-inverse"></i>
                        </span>
                        <h4 class="my-3">Problemática</h4>
                        <p class="text-muted">Anteriormente, la reserva de auditorios en la UTM se gestionaba de forma manual, causando conflictos de horario, falta de transparencia y lentitud en los procesos académicos.</p>
                    </div>
                    <div class="col-md-6">
                        <span class="fa-stack fa-4x">
                            <i class="fas fa-circle fa-stack-2x text-success"></i>
                            <i class="fas fa-lightbulb fa-stack-1x fa-inverse"></i>
                        </span>
                        <h4 class="my-3">Nuestro Objetivo</h4>
                        <p class="text-muted">Ofrecer una plataforma digital centralizada que automatice las solicitudes, permita la consulta en tiempo real y optimice el uso de los espacios institucionales.</p>
                    </div>
                </div>
            </div>
        </section>

          <section class="page-section bg-light" id="equipo">
    <div class="container text-center">
        <h2 class="section-heading text-uppercase">Equipo de Desarrollo</h2>
        <p class="text-muted mb-5">Estudiantes de TI - 5to Cuatrimestre</p>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="p-4 border rounded shadow-sm bg-white h-100">
                    <i class="fas fa-layer-group fa-3x mb-3 text-primary"></i>
                    <h5 class="fw-bold">Wilmer Ernesto Lobato Alcantar</h5>
                    <p class="text-muted small">Desarrollador Full-Stack</p>
                    <p class="x-small">Responsable de la lógica del sistema (backend), interfaz de usuario (frontend) e integración general.</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="p-4 border rounded shadow-sm bg-white h-100">
                    <i class="fas fa-palette fa-3x mb-3 text-primary"></i>
                    <h5 class="fw-bold">Andrea Urueta Rodriquez</h5>
                    <p class="text-muted small">Especialista en Frontend</p>
                    <p class="x-small">Encargada del diseño visual y la interfaz de usuario, priorizando la experiencia del usuario (UX).</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="p-4 border rounded shadow-sm bg-white h-100">
                    <i class="fas fa-microchip fa-3x mb-3 text-primary"></i>
                    <h5 class="fw-bold">Ivana Yamilet Diaz Mozqueda</h5>
                    <p class="text-muted small">Analista de Sistemas</p>
                    <p class="x-small">Definición de requisitos, análisis estructural del sistema y organización funcional del proyecto.</p>
                </div>
            </div>
        </div>
    </div>
</section>

       <section class="page-section bg-light" id="tecnologia">
    <div class="container">
        <div class="text-center">
            <h2 class="section-heading text-uppercase">Tecnología y Herramientas</h2>
            <h3 class="section-subheading text-muted">Stack tecnológico y entorno de desarrollo del proyecto SIRA.</h3>
        </div>
        
        <div class="row text-center mt-5">
            <div class="col-md-4 mb-4">
                <i class="fab fa-html5 fa-4x mb-3" style="color: #E34F26;"></i>
                <h5 class="fw-bold">HTML5 & CSS3</h5>
                <p class="text-muted small">Maquetación semántica y estilos institucionales con Bootstrap 5.</p>
            </div>
            <div class="col-md-4 mb-4">
                <i class="fab fa-js-square fa-4x mb-3" style="color: #F7DF1E;"></i>
                <h5 class="fw-bold">JavaScript</h5>
                <p class="text-muted small">Lógica de cliente, validaciones y peticiones asíncronas con Fetch API.</p>
            </div>
            <div class="col-md-4 mb-4">
                <i class="fab fa-node-js fa-4x mb-3" style="color: #339933;"></i>
                <h5 class="fw-bold">Node.js / PHP</h5>
                <p class="text-muted small">Procesamiento de datos en el servidor y servicios de mensajería SMTP.</p>
            </div>
        </div>
        
        <div class="row text-center">
            <div class="col-md-4 mb-4">
                <i class="fas fa-server fa-4x mb-3" style="color: #fb7e14;"></i>
                <h5 class="fw-bold">XAMPP / MariaDB</h5>
                <p class="text-muted small">Gestión de base de datos relacional y entorno de pruebas local.</p>
            </div>
            <div class="col-md-4 mb-4">
                <i class="fas fa-code fa-4x mb-3" style="color: #007ACC;"></i>
                <h5 class="fw-bold">VS Code</h5>
                <p class="text-muted small">IDE principal para la codificación, depuración y gestión de scripts.</p>
            </div>
            <div class="col-md-4 mb-4">
                <i class="fab fa-git-alt fa-4x mb-3" style="color: #F05032;"></i>
                <h5 class="fw-bold">Git & WinSCP</h5>
                <p class="text-muted small">Control de versiones y despliegue seguro al servidor de la UTM.</p>
            </div>
        </div>
    </div>
</section>

        <section class="page-section bg-light" id="equipo">
            <div class="container text-center">
                <h2 class="section-heading text-uppercase">Equipo de Desarrollo</h2>
                <p class="text-muted mb-5">Estudiantes de Tecnologías de la Información - 6to Cuatrimestre</p>
                <div class="row justify-content-center">
                    <div class="col-md-4">
                        <div class="p-3 border rounded shadow-sm bg-white">
                            <h5 class="mb-0">Wilmer Ernesto Lobato Alcantar</h5>
                            <small class="text-primary fw-bold">Full-Stack / DB Admin</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 border rounded shadow-sm bg-white">
                            <h5 class="mb-0">[Nombre Integrante 2]</h5>
                            <small class="text-primary fw-bold">UI Designer</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 border rounded shadow-sm bg-white">
                            <h5 class="mb-0">[Nombre Integrante 3]</h5>
                            <small class="text-primary fw-bold">Frontend Dev</small>
                        </div>
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