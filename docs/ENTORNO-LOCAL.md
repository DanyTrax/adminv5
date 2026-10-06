# Entorno local AdminV5

## Requisitos
- PHP 8.x (CLI)
- MySQL (Homebrew) corriendo

## Arranque

```bash
cd "/Users/soporte/Desktop/repos/adminv5-soft/Sin título"
php scripts/local-setup.php          # crea BD + seed (solo primera vez o reset)
php -S 127.0.0.1:8080 scripts/router.php
```

Abrir: http://127.0.0.1:8080/

## Credenciales locales
| Usuario | Password | Perfil |
|---------|----------|--------|
| admin | admin | Administrador |
| vendedor | admin | Vendedor |
| contador | admin | Contador |
| especial | admin | Especial |
| transportador | admin | Transportador |

## Bases de datos
- Local: `adminv5_local`
- Central: `adminv5_central`
- User MySQL: `adminv5` / `adminv5_local`
- Config: `config.database.php` (generado por el setup)

## Revisión automática de menús

```bash
php scripts/review-modulos.php --http=http://127.0.0.1:8080 --user=admin --pass=admin
```

Salida: `docs/REVISION-PUNTO-A-PUNTO.md`
