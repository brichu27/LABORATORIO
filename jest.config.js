module.exports = {
  testEnvironment: 'jsdom',               // antes 'node': el DOM no existia salvo por el docblock
  testMatch: ['**/tests/**/*.test.js'],
  collectCoverageFrom: ['js/validaciones.js', 'js/cargar-mas.js'],
};
