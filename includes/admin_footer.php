</main>
<script src="../js/validaciones.js"></script>
<script>
// Confirmacion para acciones destructivas (bajas)
document.querySelectorAll('form[data-confirmar]').forEach(function (f) {
    f.addEventListener('submit', function (e) {
        if (!confirm(f.dataset.confirmar)) { e.preventDefault(); }
    });
});
</script>
</body>
</html>
