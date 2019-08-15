/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you require will output into a single css file (app.css in this case)
require('../css/flatly_bootstrap.min.css');
require('../css/app.scss');

const moment = require('moment');
moment.locale('fr');

document.querySelectorAll('time').forEach(time => {
    time.textContent = moment(time.getAttribute('data-datetime')).fromNow();
})

// Need jQuery? Install it with "yarn add jquery", then uncomment to require it.
// const $ = require('jquery');
