const mobileMenuBtn = document.querySelector('#mobile-menu')
const sidebar = document.querySelector('.sidebar')
const mobileCerrarBtn = document.querySelector('#cerrar-menu')

if(mobileMenuBtn) {
    mobileMenuBtn.addEventListener('click', function() {
        sidebar.classList.add('mostrar')
    })
}

if(mobileCerrarBtn) {
    mobileCerrarBtn.addEventListener('click', function() {
        sidebar.classList.add('ocultar')
        setTimeout(() => {
            sidebar.classList.remove('mostrar')
            sidebar.classList.remove('ocultar')
        }, 500);
    })
}


//Eliminar clase cuando pantalla crece
window.addEventListener('resize', function() {
    const anchoPantalla = document.body.clientWidth
    if(anchoPantalla >= 768) {
        sidebar.classList.remove('mostrar')
    }
})