<style>
/* Estilos adicionales para crear despacho */
.producto-agregado {
    animation: slideInRight 0.5s ease;
}

@keyframes slideInRight {
    from {
        opacity: 0;
        transform: translateX(100%);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.producto-eliminado {
    animation: slideOutLeft 0.3s ease;
}

@keyframes slideOutLeft {
    from {
        opacity: 1;
        transform: translateX(0);
    }
    to {
        opacity: 0;
        transform: translateX(-100%);
    }
}

.stock-alert {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.5; }
    100% { opacity: 1; }
}

.busqueda-producto-highlight {
    background-color: #fff3cd !important;
    border-color: #ffeeba !important;
}

.validacion-exitosa {
    border-color: #28a745 !important;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25) !important;
}

.validacion-error {
    border-color: #dc3545 !important;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
}

/* Responsive para móviles */
@media (max-width: 768px) {
    .btn-group .btn {
        display: block;
        margin-bottom: 5px;
    }
    
    #tablaProductosDespacho {
        font-size: 12px;
    }
    
    .modal-lg {
        width: 95%;
    }
}
</style>