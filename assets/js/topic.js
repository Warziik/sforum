const axios = require('axios');

document.querySelectorAll('.js-responseEditButton').forEach(response => {
    response.addEventListener('click', e => {
        e.preventDefault();

        let parent = response.parentElement.parentElement.parentElement.parentElement.parentElement.parentElement;
        parent.style.display = 'none';
        let editDiv = parent.nextElementSibling;
        editDiv.classList.remove('hidden');

        let form = editDiv.querySelector('form');
        let content = form.querySelector('textarea[name=content]');
        let contentValue = content.value;

        let button = form.querySelector('button[type=submit]');
        let buttonText = button.innerHTML;
        form.addEventListener('submit', e => {
            e.preventDefault();
            let token = form.querySelector('input[name=_token]');
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            axios.post(form.action, { content: content.value, token: token.value })
                .then(response => {
                    button.disabled = false;
                    button.innerHTML = buttonText;
                    if (response.status === 200 && response.data.error === undefined) {
                        editDiv.classList.add('hidden');
                        parent.style.display = 'block';
                        parent.querySelector('.responseContent').innerHTML = response.data.content;
                        content.value = response.data.content;
                    } else {
                        let errorSpan = document.createElement('span');
                        errorSpan.className = 'invalid-feedback';
                        errorSpan.textContent = response.data.error.detail;
                        content.parentElement.appendChild(errorSpan);
                        content.classList.add('is-invalid');
                    }
                })
        })

        let cancelEditResponseAction = form.querySelector('.js-cancelEditResponseAction');
        cancelEditResponseAction.addEventListener('click', e => {
            e.preventDefault();
            button.disabled = false;
            button.innerHTML = buttonText;
            editDiv.classList.add('hidden');
            parent.style.display = 'block';
            content.value = contentValue;
        });
    });
});
