const express = require('express');
const app = express();
const path = require('path');
const db = require('./config/db_local'); // Importa tu conexión a la IP 192.168.99.3

// Configurar EJS como motor de plantillas
app.set('view engine', 'ejs');
app.set('views', path.join(__dirname, 'views'));

// Servir archivos estáticos (CSS, imágenes)
app.use('/assets', express.static(path.join(__dirname, 'assets')));

// Middleware para leer datos de formularios
app.use(express.urlencoded({ extended: false }));

// --- RUTAS ---

// 1. Mostrar el Login
app.get('/', (req, res) => {
    res.render('index'); 
});

// 2. Procesar el Login (Sustituye a autenticacion.php)
app.post('/auth', (req, res) => {
    const { matricula, password } = req.body;

    const sql = 'SELECT * FROM usuarios WHERE matricula = ? AND password = ?';
    
    db.query(sql, [matricula, password], (err, results) => {
        if (err) {
            console.error("Error en la consulta:", err);
            return res.send("Error al conectar con el servidor de la UTM");
        }

        if (results.length > 0) {
            const usuario = results[0];
            
            // Redirección lógica según el perfil de tu tabla
            if (usuario.perfil === 'Administrador') {
                res.redirect('/panel_admin');
            } else {
                res.redirect('/panel_usuario');
            }
        } else {
            // Si los datos son incorrectos
            res.send("<script>alert('Matrícula o contraseña incorrecta'); window.location='/';</script>");
        }
    });
});

// 3. Rutas para los Paneles
app.get('/panel_admin', (req, res) => {
    // Aquí le pasamos el dato que la etiqueta <%= nombre %> está esperando
    res.render('panel_admin', { nombre: 'Wilmer Lobato' }); 
});
app.get('/panel_usuario', (req, res) => {
    res.render('panel_usuario', { nombre: 'Usuario Regular' }); // Debe existir views/panel_usuario.ejs
});

app.listen(3000, () => {
    console.log('Servidor Node corriendo en http://localhost:3000');
});