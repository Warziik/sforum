let title = document.querySelector('input[id=topic_title]');
let span = document.querySelector('span#js-liveTitle');

title.addEventListener('keyup', e => {
    span.textContent = title.value;
})