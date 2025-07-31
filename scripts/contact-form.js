document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('contactForm');
    const messageDiv = document.getElementById('formMessage');

    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Очищаем предыдущие ошибки
            clearErrors();
            
            // Получаем данные формы
            const formData = new FormData(form);
            
            // Показываем индикатор загрузки
            showMessage('Отправка сообщения...', 'info');
            
            // Отправляем данные через AJAX
            fetch('process_contact.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Успешная отправка
                    showMessage(data.message, 'success');
                    form.reset();
                } else {
                    // Ошибка
                    if (data.errors) {
                        // Показываем ошибки валидации
                        showFieldErrors(data.errors);
                        showMessage(data.error, 'error');
                    } else {
                        // Общая ошибка
                        showMessage(data.error, 'error');
                    }
                }
            })
            .catch(error => {
                console.error('Ошибка:', error);
                showMessage('Произошла ошибка при отправке формы. Попробуйте позже.', 'error');
            });
        });
    }

    // Функция для отображения сообщений
    function showMessage(message, type) {
        if (messageDiv) {
            messageDiv.textContent = message;
            messageDiv.className = `feedback-form__message feedback-form__message--${type}`;
            messageDiv.style.display = 'block';
            
            // Автоматически скрываем сообщение через 5 секунд
            setTimeout(() => {
                messageDiv.style.display = 'none';
            }, 5000);
        }
    }

    // Функция для очистки ошибок
    function clearErrors() {
        // Убираем классы ошибок с полей
        const errorFields = form.querySelectorAll('.field__control--error');
        errorFields.forEach(field => {
            field.classList.remove('field__control--error');
        });

        // Убираем класс ошибки с чекбокса
        const errorCheckbox = form.querySelector('.checkbox--error');
        if (errorCheckbox) {
            errorCheckbox.classList.remove('checkbox--error');
        }

        // Скрываем сообщение
        if (messageDiv) {
            messageDiv.style.display = 'none';
        }
    }

    // Функция для отображения ошибок полей
    function showFieldErrors(errors) {
        Object.keys(errors).forEach(fieldName => {
            const field = form.querySelector(`[name="${fieldName}"]`);
            if (field) {
                if (fieldName === 'agreement') {
                    // Для чекбокса добавляем класс к label
                    const checkboxLabel = field.closest('.checkbox');
                    if (checkboxLabel) {
                        checkboxLabel.classList.add('checkbox--error');
                    }
                } else {
                    // Для остальных полей добавляем класс к input/textarea
                    field.classList.add('field__control--error');
                }
            }
        });
    }
}); 