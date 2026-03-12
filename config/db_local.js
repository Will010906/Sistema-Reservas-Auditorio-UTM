const mysql = require('mysql2');

// Configuración espejo de tu db_local.php
const connection = mysql.createConnection({
  host: 'localhost',      // Tu servidor local en XAMPP
  user: 'root',           // Usuario por defecto
  password: '',           // Sin contraseña, como lo tienes en el PHP
  database: 'reservacionauditorios' // El nombre de tu base de datos local
});

connection.connect((err) => {
  if (err) {
    console.error('❌ Error conectando a XAMPP:', err.message);
    return;
  }
  console.log('✅ Conexión LOCAL exitosa. Trabajando desde casa.');
});

module.exports = connection;