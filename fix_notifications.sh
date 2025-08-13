#!/bin/bash

# Скрипт для исправления уведомлений во всех файлах settings

FILES=(
    "resources/views/admin/settings/dictionary-symptoms.blade.php"
    "resources/views/admin/settings/lab-test-params.blade.php"
    "resources/views/admin/settings/lab-test-types.blade.php"
    "resources/views/admin/settings/specialties.blade.php"
    "resources/views/admin/settings/species.blade.php"
    "resources/views/admin/settings/statuses.blade.php"
    "resources/views/admin/settings/suppliers.blade.php"
    "resources/views/admin/settings/units.blade.php"
)

echo "Исправление уведомлений в файлах settings..."
echo ""

for file in "${FILES[@]}"; do
    echo "Обработка файла: $file"
    
    if [ ! -f "$file" ]; then
        echo "  Файл не найден, пропускаем"
        continue
    fi
    
    # 1. Добавляем CSS стили если их нет
    if ! grep -q "notification-toast" "$file"; then
        echo "  Добавляем CSS стили..."
        # Добавляем стили после @section('title')
        sed -i '/@section('\''title'\'')/a\
\
@push('\''styles'\'')\
<style>\
    /* Стили для Bootstrap уведомлений */\
    .notification-toast {\
        animation: slideInRight 0.3s ease-out;\
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);\
        border: none;\
        border-radius: 0.5rem;\
        margin-bottom: 0.5rem;\
    }\
    \
    .notification-toast .toast-header {\
        border-bottom: 1px solid rgba(0, 0, 0, 0.1);\
        background-color: transparent;\
    }\
    \
    .notification-toast .toast-body {\
        padding: 0.75rem 1rem;\
        font-size: 0.9rem;\
        line-height: 1.4;\
    }\
    \
    /* Стили для кнопки закрытия в зависимости от типа уведомления */\
    .notification-toast.bg-warning .btn-close {\
        filter: invert(1) grayscale(100%) brightness(0);\
        opacity: 1;\
    }\
    \
    @keyframes slideInRight {\
        from {\
            transform: translateX(100%);\
            opacity: 0;\
        }\
        to {\
            transform: translateX(0);\
            opacity: 1;\
        }\
    }\
    \
    @keyframes slideOutRight {\
        from {\
            transform: translateX(0);\
            opacity: 1;\
        }\
        to {\
            transform: translateX(100%);\
            opacity: 0;\
        }\
    }\
    \
    .notification-toast.fade-out {\
        animation: slideOutRight 0.3s ease-in forwards;\
    }\
</style>\
@endpush' "$file"
    fi
    
    # 2. Добавляем HTML контейнер если его нет
    if ! grep -q "notifications-container" "$file"; then
        echo "  Добавляем HTML контейнер..."
        # Добавляем контейнер после заголовка
        sed -i '/<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">/,/<\/div>/a\
\
<!-- Bootstrap уведомления -->\
<div id="notifications-container" class="position-fixed top-0 end-0 p-3" style="z-index: 1060; max-width: 400px;">\
</div>' "$file"
    fi
    
    # 3. Исправляем одинарные кавычки в onclick
    echo "  Исправляем кавычки в onclick..."
    sed -i "s/onclick='deleteRow(/onclick=\"deleteRow(/g" "$file"
    
    # 4. Исправляем HTTP методы
    echo "  Исправляем HTTP методы..."
    # PATCH -> POST с _method
    sed -i "s/method: 'PATCH'/method: 'POST'/g" "$file"
    sed -i "s/body: JSON.stringify(data)/body: JSON.stringify({...data, _method: 'PATCH'})/g" "$file"
    
    # DELETE -> POST с _method
    sed -i "s/method: 'DELETE'/method: 'POST'/g" "$file"
    sed -i "s/headers: {/headers: {\n                    'Content-Type': 'application\/json',/g" "$file"
    sed -i "/method: 'POST'/a\                body: JSON.stringify({ _method: 'DELETE' })" "$file"
    
    # 5. Исправляем обработку ответов
    echo "  Исправляем обработку ответов..."
    # Заменяем простую обработку на проверку response.ok
    sed -i "s/\.then(response => response\.json())/.then(response => {\n                if (!response.ok) {\n                    throw new Error(\`HTTP \${response.status}: \${response.statusText}\`);\n                }\n                return response.json();\n            })/g" "$file"
    
    # 6. Заменяем alert на функции уведомлений
    echo "  Заменяем alert на функции уведомлений..."
    sed -i "s/alert('Ошибка при создании/showError('Ошибка при создании/g" "$file"
    sed -i "s/alert('Произошла ошибка при создании/showError('Произошла ошибка при создании/g" "$file"
    sed -i "s/alert('Ошибка при удалении/showError('Ошибка при удалении/g" "$file"
    sed -i "s/alert('Произошла ошибка при удалении/showError('Произошла ошибка при удалении/g" "$file"
    sed -i "s/alert('Ошибка при сохранении/showError('Ошибка при сохранении/g" "$file"
    
    # 7. Добавляем функции уведомлений если их нет
    if ! grep -q "showNotification" "$file"; then
        echo "  Добавляем функции уведомлений..."
        # Добавляем функции в начало скрипта
        sed -i '/<script>/a\
// Функции для работы с Bootstrap уведомлениями\
function showNotification(message, type = '\''info'\'', title = null) {\
    const container = document.getElementById('\''notifications-container'\'');\
    if (!container) return;\
    \
    const toastId = '\''toast-'\'' + Date.now() + '\''-'\'' + Math.random().toString(36).substr(2, 9);\
    \
    // Определяем иконку и цвет в зависимости от типа\
    let icon, bgClass, textClass;\
    switch (type) {\
        case '\''success'\'':\
            icon = '\''bi-check-circle-fill'\'';\
            bgClass = '\''bg-success'\'';\
            textClass = '\''text-white'\'';\
            title = title || '\''Успешно'\'';\
            break;\
        case '\''error'\'':\
        case '\''danger'\'':\
            icon = '\''bi-exclamation-triangle-fill'\'';\
            bgClass = '\''bg-danger'\'';\
            textClass = '\''text-white'\'';\
            title = title || '\''Ошибка'\'';\
            break;\
        case '\''warning'\'':\
            icon = '\''bi-exclamation-circle-fill'\'';\
            bgClass = '\''bg-warning'\'';\
            textClass = '\''text-dark'\'';\
            title = title || '\''Предупреждение'\'';\
            break;\
        case '\''info'\'':\
        default:\
            icon = '\''bi-info-circle-fill'\'';\
            bgClass = '\''bg-info'\'';\
            textClass = '\''text-white'\'';\
            title = title || '\''Информация'\'';\
            break;\
    }\
    \
    const toastHtml = `\
        <div id="${toastId}" class="toast notification-toast ${bgClass} ${textClass}" role="alert" aria-live="assertive" aria-atomic="true">\
            <div class="toast-header ${textClass}">\
                <i class="bi ${icon} me-2"></i>\
                <strong class="me-auto">${title}</strong>\
                <button type="button" class="btn-close ${type === '\''warning'\'' ? '\'''\'' : '\''btn-close-white'\''}" data-bs-dismiss="toast" aria-label="Close"></button>\
            </div>\
            <div class="toast-body">\
                ${message}\
            </div>\
        </div>\
    `;\
    \
    container.insertAdjacentHTML('\''beforeend'\'', toastHtml);\
    \
    const toastElement = document.getElementById(toastId);\
    const toast = new bootstrap.Toast(toastElement, {\
        autohide: true,\
        delay: type === '\''error'\'' || type === '\''danger'\'' ? 8000 : 5000\
    });\
    \
    toast.show();\
    \
    // Автоматическое удаление элемента после скрытия\
    toastElement.addEventListener('\''hidden.bs.toast'\'', function() {\
        toastElement.remove();\
    });\
    \
    return toast;\
}\
\
function showError(message, title = '\''Ошибка'\'') {\
    return showNotification(message, '\''error'\'', title);\
}\
\
function showSuccess(message, title = '\''Успешно'\'') {\
    return showNotification(message, '\''success'\'', title);\
}\
\
function showWarning(message, title = '\''Предупреждение'\'') {\
    return showNotification(message, '\''warning'\'', title);\
}\
\
function showInfo(message, title = '\''Информация'\'') {\
    return showNotification(message, '\''info'\'', title);\
}' "$file"
    fi
    
    # 8. Исправляем функцию showNotification для совместимости
    if grep -q "window.showNotification" "$file"; then
        echo "  Исправляем функцию showNotification..."
        sed -i '/window.showNotification = function(message, type) {/,/}/c\
    // Функция для совместимости с существующим кодом\
    window.showNotification = function(message, type) {\
        // Используем новые Bootstrap уведомления\
        if (type === '\''error'\'') {\
            showError(message);\
        } else if (type === '\''success'\'') {\
            showSuccess(message);\
        } else if (type === '\''warning'\'') {\
            showWarning(message);\
        } else {\
            showInfo(message);\
        }\
    }' "$file"
    fi
    
    echo "  Файл $file обработан"
    echo ""
done

echo "Исправление завершено!"
echo ""
echo "Проверьте все файлы на наличие ошибок и протестируйте функциональность."
