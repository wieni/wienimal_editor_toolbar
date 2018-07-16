const $editbar = document.querySelector('[data-editbar]');
const $editbarToggle = document.querySelector('[data-editbar-toggle]');

if (JSON.parse(localStorage.getItem('editbar')) === true) {
    $editbar.classList.add('open');
}

$editbarToggle.addEventListener('click', function() {
    localStorage.setItem('editbar', !JSON.parse(localStorage.getItem('editbar')));
    $editbar.classList.add('has-transition');
    $editbar.classList.toggle('open');
});
