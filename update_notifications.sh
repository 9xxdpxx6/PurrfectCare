#!/bin/bash

# Скрипт для обновления уведомлений во всех файлах settings

FILES=(
    "resources/views/admin/settings/dictionary-diagnoses.blade.php"
    "resources/views/admin/settings/dictionary-symptoms.blade.php"
    "resources/views/admin/settings/lab-test-params.blade.php"
    "resources/views/admin/settings/lab-test-types.blade.php"
    "resources/views/admin/settings/specialties.blade.php"
    "resources/views/admin/settings/species.blade.php"
    "resources/views/admin/settings/statuses.blade.php"
    "resources/views/admin/settings/suppliers.blade.php"
    "resources/views/admin/settings/units.blade.php"
)

# CSS стили для уведомлений
NOTIFICATION_STYLES='@push("styles")
<style>
    /* Стили для Bootstrap уведомлений */
    .notification-toast {
        animation: slideInRight 0.3s ease-out;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        border: none;
        border-radius: 0.5rem;
        margin-bottom: 0.5rem;
    }
    
    .notification-toast .toast-header {
        border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        background-color: transparent;
    }
    
    .notification-toast .toast-body {
        padding: 0.75rem 1rem;
        font-size: 0.9rem;
        line-height: 1.4;
    }
    
    /* Стили для кнопки закрытия в зависимости от типа уведомления */
    .notification-toast.bg-warning .btn-close {
        filter: invert(1) grayscale(100%) brightness(0);
        opacity: 1;
    }
    
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    .notification-toast.fade-out {
        animation: slideOutRight 0.3s ease-in forwards;
    }
</style>
@endpush'

# HTML контейнер для уведомлений
NOTIFICATION_CONTAINER='<!-- Bootstrap уведомления -->
<div id="notifications-container" class="position-fixed top-0 end-0 p-3" style="z-index: 1060; max-width: 400px;">
</div>'

# JavaScript функции для уведомлений
NOTIFICATION_FUNCTIONS='// Функции для работы с Bootstrap уведомлениями
function showNotification(message, type = "info", title = null) {
    const container = document.getElementById("notifications-container");
    if (!container) return;
    
    const toastId = "toast-" + Date.now() + "-" + Math.random().toString(36).substr(2, 9);
    
    // Определяем иконку и цвет в зависимости от типа
    let icon, bgClass, textClass;
    switch (type) {
        case "success":
            icon = "bi-check-circle-fill";
            bgClass = "bg-success";
            textClass = "text-white";
            title = title || "Успешно";
            break;
        case "error":
        case "danger":
            icon = "bi-exclamation-triangle-fill";
            bgClass = "bg-danger";
            textClass = "text-white";
            title = title || "Ошибка";
            break;
        case "warning":
            icon = "bi-exclamation-circle-fill";
            bgClass = "bg-warning";
            textClass = "text-dark";
            title = title || "Предупреждение";
            break;
        case "info":
        default:
            icon = "bi-info-circle-fill";
            bgClass = "bg-info";
            textClass = "text-white";
            title = title || "Информация";
            break;
    }
    
    const toastHtml = `
        <div id="${toastId}" class="toast notification-toast ${bgClass} ${textClass}" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header ${textClass}">
                <i class="bi ${icon} me-2"></i>
                <strong class="me-auto">${title}</strong>
                <button type="button" class="btn-close ${type === "warning" ? "" : "btn-close-white"}" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML("beforeend", toastHtml);
    
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, {
        autohide: true,
        delay: type === "error" || type === "danger" ? 8000 : 5000
    });
    
    toast.show();
    
    // Автоматическое удаление элемента после скрытия
    toastElement.addEventListener("hidden.bs.toast", function() {
        toastElement.remove();
    });
    
    return toast;
}

function showError(message, title = "Ошибка") {
    return showNotification(message, "error", title);
}

function showSuccess(message, title = "Успешно") {
    return showNotification(message, "success", title);
}

function showWarning(message, title = "Предупреждение") {
    return showNotification(message, "warning", title);
}

function showInfo(message, title = "Информация") {
    return showNotification(message, "info", title);
}'

echo "Скрипт для обновления уведомлений создан!"
echo "Необходимо вручную обновить следующие файлы:"
for file in "${FILES[@]}"; do
    echo "- $file"
done
echo ""
echo "Для каждого файла нужно:"
echo "1. Добавить CSS стили после @section('title')"
echo "2. Добавить HTML контейнер после заголовка"
echo "3. Добавить JavaScript функции в начало скрипта"
echo "4. Заменить все alert() на соответствующие функции уведомлений"
echo "5. Исправить одинарные кавычки в onclick атрибутах"
