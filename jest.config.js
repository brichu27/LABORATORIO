module.exports = {
  testEnvironment: 'jsdom',               // los tests usan document y eventos del navegador
  testMatch: ['**/tests/**/*.test.js'],
  collectCoverageFrom: ['js/validaciones.js', 'js/cargar-mas.js'],
};
