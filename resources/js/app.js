import '../scss/app.scss';
import * as bootstrap from 'bootstrap'

// Tooltip plugin initialization.
const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))

// Extra javascript code can go here.
// ...
