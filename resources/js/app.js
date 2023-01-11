import '../scss/app.scss';
import '../../node_modules/bootstrap/dist/js/bootstrap.bundle.min'

// Tooltip plugin initialization.
const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))

// Extra javascript code can go here.
// ...
