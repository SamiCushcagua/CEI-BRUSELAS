// ===== SISTEMA DE CALIFICACIONES =====

// Variables globales
let currentSubjectId = null;
let currentTrimester = null;
let currentYear = null;

// Inicialización cuando se carga el DOM
document.addEventListener('DOMContentLoaded', function() {
    initializeGradeSystem();
});

// Función principal de inicialización
function initializeGradeSystem() {
    // Obtener datos de la página si están disponibles
    const subjectElement = document.querySelector('[data-subject-id]');
    if (subjectElement) {
        currentSubjectId = subjectElement.dataset.subjectId;
    }
    
    const trimesterElement = document.querySelector('[data-trimester]');
    if (trimesterElement) {
        currentTrimester = trimesterElement.dataset.trimester;
    }
    
    const yearElement = document.querySelector('[data-year]');
    if (yearElement) {
        currentYear = yearElement.dataset.year;
    }
    
    // Inicializar eventos
    initializeGradeInputs();
    initializeSettingsForm();
    initializeReportsForm();
}

// ===== CALIFICACIONES =====

// Inicializar inputs de calificaciones
function initializeGradeInputs() {
    const gradeInputs = document.querySelectorAll('.grade-input');
    
    gradeInputs.forEach(input => {
        input.addEventListener('input', function() {
            calculateAverage(this.dataset.studentId);
        });
        
        input.addEventListener('blur', function() {
            validateGradeInput(this);
        });
    });
}

// Calcular promedio de un estudiante
function calculateAverage(studentId) {
    const inputs = document.querySelectorAll(`[data-student-id="${studentId}"]`);
    let total = 0;
    let count = 0;
    
    inputs.forEach(input => {
        if (input.classList.contains('grade-input') && input.value !== '') {
            const value = parseFloat(input.value);
            if (!isNaN(value) && value >= 0 && value <= 100) {
                total += value;
                count++;
            }
        }
    });
    
    const average = count > 0 ? total / count : 0;
    const averageElement = document.querySelector(`.average-score[data-student-id="${studentId}"]`);
    if (averageElement) {
        averageElement.textContent = average.toFixed(2);
        
        // Cambiar color según el promedio
        if (average >= 70) {
            averageElement.style.color = '#10b981'; // Verde
        } else if (average >= 60) {
            averageElement.style.color = '#f59e0b'; // Amarillo
        } else {
            averageElement.style.color = '#ef4444'; // Rojo
        }
    }
}

// Validar input de calificación
function validateGradeInput(input) {
    const value = parseFloat(input.value);
    
    if (input.value !== '' && (isNaN(value) || value < 0 || value > 100)) {
        input.style.borderColor = '#ef4444';
        input.style.backgroundColor = '#fef2f2';
        showNotification('La calificación debe estar entre 0 y 100', 'error');
    } else {
        input.style.borderColor = '#d1d5db';
        input.style.backgroundColor = 'white';
    }
}

// Guardar calificaciones de un estudiante
function saveStudentGrade(studentId) {
    if (!currentSubjectId) {
        showNotification('Error: No se ha seleccionado una materia', 'error');
        return;
    }

    const formData = {
        student_id: studentId,
        subject_id: currentSubjectId,
        trimester: currentTrimester,
        year: currentYear,
        task_score: document.querySelector(`[data-student-id="${studentId}"][data-field="task_score"]`)?.value || null,
        exam_score1: document.querySelector(`[data-student-id="${studentId}"][data-field="exam_score1"]`)?.value || null,
        exam_score2: document.querySelector(`[data-student-id="${studentId}"][data-field="exam_score2"]`)?.value || null,
        participation_score: document.querySelector(`[data-student-id="${studentId}"][data-field="participation_score"]`)?.value || null,
        bible_score: document.querySelector(`[data-student-id="${studentId}"][data-field="bible_score"]`)?.value || null,
        text_score: document.querySelector(`[data-student-id="${studentId}"][data-field="text_score"]`)?.value || null,
        other_score: document.querySelector(`[data-student-id="${studentId}"][data-field="other_score"]`)?.value || null
    };

    const saveBtn = document.querySelector(`[data-student-id="${studentId}"].save-btn`);
    if (saveBtn) {
        saveBtn.textContent = '⏳ Guardando...';
        saveBtn.disabled = true;
    }

    fetch('/grades', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(formData)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return response.json();
        } else {
            throw new Error('Response is not JSON');
        }
    })
    .then(data => {
        if (data.success) {
            showNotification('Calificaciones guardadas exitosamente', 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showNotification(data.message || 'Error al guardar calificaciones', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error al guardar calificaciones', 'error');
    })
    .finally(() => {
        if (saveBtn) {
            saveBtn.textContent = '💾 Guardar';
            saveBtn.disabled = false;
        }
    });
}
// Exportar a PDF
function exportToPDF() {
    if (!currentSubjectId) {
        showNotification('No se puede exportar: ID de materia no encontrado', 'error');
        return;
    }
    
    const url = `/grades/export/pdf?subject=${currentSubjectId}&trimester=${currentTrimester}&year=${currentYear}`;
    window.open(url, '_blank');
}

// ===== CONFIGURACIÓN DE CALIFICACIONES =====

// Inicializar formulario de configuración
function initializeSettingsForm() {
    const addForm = document.getElementById('addSettingForm');
    if (addForm) {
        addForm.addEventListener('submit', handleAddSetting);
    }
    
    // Inicializar inputs de configuración existente
    const settingInputs = document.querySelectorAll('.setting-input');
    settingInputs.forEach(input => {
        input.addEventListener('input', function() {
            validateSettingInput(this);
        });
    });
}

// Manejar agregar nueva configuración
// Manejar agregar nueva configuración
function handleAddSetting(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    
    // Convertir FormData a JSON
    const data = {};
    for (let [key, value] of formData.entries()) {
        data[key] = value;
    }
    
    fetch('/grade-settings', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCSRFToken(),
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        // Verificar si la respuesta es JSON
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return response.json();
        } else {
            throw new Error(`Error del servidor: ${response.status} ${response.statusText}`);
        }
    })
    .then(data => {
        if (data.success) {
            showNotification('Configuración agregada correctamente', 'success');
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showNotification(data.message || 'Error al agregar la configuración', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification(`Error: ${error.message}`, 'error');
    });
}

// Actualizar configuración existente
// Actualizar configuración existente
function updateSetting(settingId) {
    const inputs = document.querySelectorAll(`[data-setting-id="${settingId}"]`);
    const data = {
        _token: getCSRFToken(),
        _method: 'PUT'
    };
    
    let isValid = true;
    inputs.forEach(input => {
        const value = input.value;
        if (value === '' || isNaN(parseFloat(value)) || parseFloat(value) < 0) {
            isValid = false;
            input.style.borderColor = '#ef4444';
        } else {
            input.style.borderColor = '#d1d5db';
            data[input.dataset.field] = parseFloat(value);
        }
    });
    
    if (!isValid) {
        showNotification('Todos los campos deben tener valores válidos', 'error');
        return;
    }
    
    fetch(`/grade-settings/${settingId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCSRFToken(),
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return response.json();
        } else {
            throw new Error(`Error del servidor: ${response.status} ${response.statusText}`);
        }
    })
    .then(data => {
        if (data.success) {
            showNotification('Configuración actualizada correctamente', 'success');
        } else {
            showNotification(data.message || 'Error al actualizar la configuración', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification(`Error: ${error.message}`, 'error');
    });
}

// Eliminar configuración
function deleteSetting(settingId) {
    if (!confirm('¿Estás seguro de que quieres eliminar esta configuración?')) {
        return;
    }
    
    fetch(`/grade-settings/${settingId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCSRFToken(),
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            _method: 'DELETE',
            _token: getCSRFToken()
        })
    })
    .then(response => {
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return response.json();
        } else {
            throw new Error(`Error del servidor: ${response.status} ${response.statusText}`);
        }
    })
    .then(data => {
        if (data.success) {
            showNotification('Configuración eliminada correctamente', 'success');
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showNotification(data.message || 'Error al eliminar la configuración', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification(`Error: ${error.message}`, 'error');
    });
}

// Validar input de configuración
function validateSettingInput(input) {
    const value = parseFloat(input.value);
    
    if (input.value !== '' && (isNaN(value) || value < 0)) {
        input.style.borderColor = '#ef4444';
        input.style.backgroundColor = '#fef2f2';
    } else {
        input.style.borderColor = '#d1d5db';
        input.style.backgroundColor = 'white';
    }
}

// ===== REPORTES =====

// Inicializar formulario de reportes
function initializeReportsForm() {
    const reportForm = document.getElementById('reportForm');
    if (reportForm) {
        reportForm.addEventListener('submit', handleGenerateReport);
    }
    
    // Cargar estadísticas al inicializar
    loadStatistics();
}

// Manejar generación de reporte
function handleGenerateReport(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const params = new URLSearchParams(formData);
    
    window.open(`/grade-reports/generate?${params.toString()}`, '_blank');
}

// Cargar estadísticas
// Función corregida en profesor-calificacion.js
function loadStatistics() {
    if (!currentSubjectId) {
        console.log('No hay subject_id disponible para cargar estadísticas');
        return;
    }
    
    const params = new URLSearchParams({
        subject_id: currentSubjectId,
        year: currentYear || new Date().getFullYear(),
        trimester: currentTrimester || 1
    });
    
    fetch(`/grades/statistics?${params.toString()}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateStatisticsDisplay(data.statistics);
        } else {
            showNotification('Error al cargar estadísticas', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error al cargar estadísticas', 'error');
    });
}
// Actualizar display de estadísticas
function updateStatisticsDisplay(stats) {
    const elements = {
        totalStudents: document.getElementById('totalStudents'),
        generalAverage: document.getElementById('generalAverage'),
        passedStudents: document.getElementById('passedStudents'),
        failedStudents: document.getElementById('failedStudents')
    };
    
    if (elements.totalStudents) {
        elements.totalStudents.textContent = stats.total_students || 0;
    }
    
    if (elements.generalAverage) {
        elements.generalAverage.textContent = (stats.general_average || 0).toFixed(2);
    }
    
    if (elements.passedStudents) {
        elements.passedStudents.textContent = stats.passed_students || 0;
    }
    
    if (elements.failedStudents) {
        elements.failedStudents.textContent = stats.failed_students || 0;
    }
}

// ===== UTILIDADES =====

// Obtener token CSRF
function getCSRFToken() {
    const token = document.querySelector('meta[name="csrf-token"]');
    return token ? token.getAttribute('content') : '';
}

// Mostrar notificación
function showNotification(message, type = 'info') {
    // Crear elemento de notificación
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    
    // Estilos de notificación
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 6px;
        color: white;
        font-weight: 500;
        z-index: 1000;
        max-width: 300px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        transform: translateX(100%);
        transition: transform 0.3s ease;
    `;
    
    // Colores según tipo
    const colors = {
        success: '#10b981',
        error: '#ef4444',
        warning: '#f59e0b',
        info: '#3b82f6'
    };
    
    notification.style.backgroundColor = colors[type] || colors.info;
    
    // Agregar al DOM
    document.body.appendChild(notification);
    
    // Animar entrada
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 100);
    
    // Remover después de 3 segundos
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

// Validar formulario
function validateForm(form) {
    const inputs = form.querySelectorAll('input[required], select[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.style.borderColor = '#ef4444';
            isValid = false;
        } else {
            input.style.borderColor = '#d1d5db';
        }
    });
    
    return isValid;
}

// Formatear número
function formatNumber(number, decimals = 2) {
    return parseFloat(number).toFixed(decimals);
}

// Debounce para inputs
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Exportar funciones globales necesarias
window.saveStudentGrade = saveStudentGrade;
window.exportToPDF = exportToPDF;
window.updateSetting = updateSetting;
window.deleteSetting = deleteSetting;
window.loadStatistics = loadStatistics;