-- Agregar estados 'parcial' y 'finalizado' a solicitudes_stock
-- Estado actual: enum('pendiente','aprobado','cancelado')
-- Después: enum('pendiente','aprobado','parcial','finalizado','cancelado')

ALTER TABLE solicitudes_stock 
MODIFY COLUMN estado ENUM('pendiente', 'aprobado', 'parcial', 'finalizado', 'cancelado') 
DEFAULT 'pendiente';
