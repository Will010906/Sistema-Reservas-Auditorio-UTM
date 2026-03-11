const mysql = require('mysql2');

// Configuración con los datos de tu equipo 11
const connection = mysql.createConnection({
  host: '192.168.99.3',
  port: 9091,
  user: 'user_equipo11',
  password: 'user_secret_password11',
  database: 'proyecto_equipo11_db'
});

connection.connect((err) => {
  if (err) {
    console.error('Error conectando a la UTM: ' + err.stack);
    return;
  }
  console.log('Conectado con éxito al servidor del laboratorio.');
});

module.exports = connection;