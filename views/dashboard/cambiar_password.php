<?php @include_once __DIR__ . '/header-dashboard.php' ?>

<div class="contenedor-sm">
    <?php @include_once __DIR__ . '/../templates/alertas.php' ?>

    <a class="enlace" href="/perfil">Volver a Perfil</a>

    <form method="post" class="formulario">
    <div class="campo">
            <label for="password_actual">Password Actual</label>
            <input 
                    type="password"
                    id="password_actual"
                    name="password_actual"
                    placeholder="Ingresa tu Password Actual">
        </div>

        <div class="campo">
            <label for="password_nuevo">Nuevo Password</label>
            <input 
                    type="password"
                    id="password_nuevo"
                    name="password_nuevo"
                    placeholder="Ingresa tu Nuevo Password">
        </div>

        <input type="submit" value="Guardar Cambios">
    </form>
</div>

<?php @include_once __DIR__ . '/footer-dashboard.php' ?>