<?php @include_once __DIR__ . '/header-dashboard.php' ?>

<div class="contenedor-sm">
    <?php @include_once __DIR__ . '/../templates/alertas.php' ?>

    <form class="formulario" method="post">
        <?php @include_once __DIR__ . '/formulario-proyecto.php' ?> 

        <input type="submit" class="boton" value="Crear Proyecto">
    </form>
</div>

<?php @include_once __DIR__ . '/footer-dashboard.php' ?>