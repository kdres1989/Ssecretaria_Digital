<footer>
    <p>© 2026 Secretaría Digital | <a href="nosotros.php">Misión, Visión e Integrantes</a></p>
</footer>
<script>
document.querySelectorAll('input[type="password"]').forEach(function (input) {
    const wrapper = document.createElement('div');
    wrapper.className = 'password-wrapper';
    input.parentNode.insertBefore(wrapper, input);
    wrapper.appendChild(input);

    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'toggle-password';
    btn.setAttribute('aria-label', 'Mostrar u ocultar contraseña');

    const ojoAbierto = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>';
    const ojoCerrado = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>';

    btn.innerHTML = ojoAbierto;
    wrapper.appendChild(btn);

    btn.addEventListener('click', function () {
        const esOculto = input.type === 'password';
        input.type = esOculto ? 'text' : 'password';
        btn.innerHTML = esOculto ? ojoCerrado : ojoAbierto;
    });
});
</script>
</body>
</html>
