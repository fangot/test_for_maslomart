BX.ready(function() {
    const form = document.getElementById('custom-form');
    if (!form) return;

    initForm();

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            return false;
        }

        BX.showWait(form);
        initForm();
        
        const formData = new FormData(form);
        formData.append('is_ajax', 'Y');

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            BX.closeWait(form);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            return response.json().catch(() => {
                return response.text();
            });
        })
        .then(data => {
            if (typeof data === 'object') {
                if (data.success) {
                    form.closest('.custom-form-container').innerHTML = `<div class="alert alert-success">${data.message || 'Форма успешно отправлена'}</div>`;
                } else {
                    showFormErrors(form, data.errors || ['Ошибка сервера']);
                }
            } else {
                handleHtmlResponse(data);
            }
        })
        .catch(error => {
            BX.closeWait(form);
            showFormErrors(form, [error.message || 'Ошибка при отправке формы']);
        });
    });

    function handleHtmlResponse(html) {
        try {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const errorContainer = doc.querySelector('.alert-danger');
            
            if (errorContainer) {
                showFormErrors(form, [errorContainer.textContent.trim()]);
            } else {
                form.closest('.custom-form-container').innerHTML = `<div class="alert alert-info">Форма успешно отправлена</div>`;
            }
        } catch (e) {
            showFormErrors(form, ['Не удалось обработать ответ сервера']);
        }
    }

    function showFormErrors(form, errors) {
        const oldAlerts = form.querySelectorAll('.alert-danger');
        oldAlerts.forEach(alert => alert.remove());

        if (errors && errors.length > 0) {
            const errorContainer = document.createElement('div');
            errorContainer.className = 'alert alert-danger mb-3';

            errors.forEach(error => {
                const errorElement = document.createElement('p');
                errorElement.textContent = error;
                errorContainer.appendChild(errorElement);
            });

            form.parentNode.insertBefore(errorContainer, form);
        }
    }

    function initForm() {
        if (BX('add-item')) {
            BX.bind(BX('add-item'), 'click', addNewItemRow);
        }

        form.addEventListener('click', function(e) {
            const target = e.target;
            if (target && target.classList.contains('remove-item')) {
                removeItemRow(target);
            }
        });

        const buttons = form.querySelectorAll('.remove-item');
        buttons.forEach(btn => {
            btn.addEventListener('click', function() {
                removeItemRow(btn);
            });
        });
    }

    function addNewItemRow() {
        const tbody = form.querySelector('tbody');
        if (!tbody) {
            console.error('Элемент tbody не найден');
            return;
        }

        const newRow = document.createElement('tr');
        newRow.className = 'item-row';
        newRow.innerHTML = `
            <td>${createBrandSelectHTML()}</td>
            <td><input type="text" class="form-control" name="items[][name]" required></td>
            <td><input type="number" class="form-control" name="items[][quantity]"></td>
            <td><input type="text" class="form-control" name="items[][packing]"></td>
            <td><input type="text" class="form-control" name="items[][client]"></td>
            <td><button type="button" class="btn btn-danger btn-sm remove-item">×</button></td>
        `;

        tbody.appendChild(newRow);
    }

    function createBrandSelectHTML() {
        let options = '<option value="">Выберите бренд</option>';
        const brands = BX.message('BRANDS_DATA') || {};

        for (const [code, name] of Object.entries(brands)) {
            options += `<option value="${name}">${name}</option>`;
        }

        return `<select class="form-select" name="items[][brand]" required>${options}</select>`;
    }

    function removeItemRow(button) {
        const row = button.closest('tr');
        if (!row) return;

        const allRows = form.querySelectorAll('tbody tr');
        
        if (allRows.length <= 1) {
            clearRow(row);
        } else {
            row.remove();
        }
    }

    function clearRow(row) {
        const fields = row.querySelectorAll('input, select');
        fields.forEach(field => {
            field.value = '';
            if (field.tagName === 'SELECT') {
                field.selectedIndex = 0;
            }
        });
    }

    function handleFormSubmit(form) {
        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            return false;
        }
        const formData = new FormData(form);

        BX.showWait(form);

        BX.ajax.submit(form, function(response) {
            BX.closeWait(form);

            try {
                const result = JSON.parse(response);
                if (result.success) {
                    if (result.redirect) {
                        window.location.href = result.redirect;
                    } else {
                        BX.onCustomEvent('OnFormSubmitSuccess', [result]);
                        const container = form.closest('.custom-form-container');
                        if (container) {
                            container.innerHTML = '<div class="alert alert-success">' + result.message + '</div>';
                        }
                    }
                } else {
                    showFormErrors(form, result.errors);
                }
            } catch (e) {
                console.error('Ошибка обработки ответа:', e);

                document.open();
                document.write(response);
                document.close();
            }
        });

        return false;
    }
});