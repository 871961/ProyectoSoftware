# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**MedHistory** is a Spanish-language medical records management system. It has a PHP/PostgreSQL backend with a vanilla JS + Tailwind CSS frontend. The entire UI is in Spanish, including variable names, DB columns, and user-facing strings.

## Commands

**Install & build frontend CSS:**
```bash
cd frontend && npm install
npm run build:css        # compile src/styles/tailwind.css → static/css/tailwind.css
npm run dev:css          # watch mode during development
```

**From root (orchestrates frontend):**
```bash
npm install              # runs install:frontend + build:frontend automatically
```

**Database setup (PostgreSQL on localhost:5432, db: medhistory):**
```bash
psql -U postgres -d medhistory -f database/schema.sql
psql -U postgres -d medhistory -f database/datos_prueba.sql   # test data (doctors + patients)
```

There is no test framework and no linter configured.

## Architecture

### Request Flow

The frontend serves static HTML files from `frontend/static/`. Each role has one HTML file (`paciente.html`, `medico.html`, `admin.html`, `dependiente.html`) that loads all tab/panel content at once. JavaScript files in `frontend/static/js/` make `fetch()` calls directly to PHP controllers:

```
frontend/static/js/dashboard.js
  → fetch('/backend/src/controllers/ConsultasController.php?accion=listar')
  → ConsultasController.php
  → ConsultaDAO.php
  → PostgreSQL
```

All API communication uses query-string `?accion=<action>` for reads and JSON POST bodies for writes. Controllers return JSON responses.

### Backend Pattern (MVC + DAO + VO)

- **`backend/src/controllers/`** — Entry points. Each controller parses `$_GET['accion']` or reads `php://input`, calls a DAO, and echoes JSON.
- **`backend/src/dao/`** — All SQL lives here. DAOs use the PDO singleton from `backend/src/config/database.php`. Use prepared statements everywhere.
- **`backend/src/vo/`** — Value Objects for passing typed data between controllers and DAOs.
- **`backend/src/config/database.php`** — PDO singleton connecting to `localhost:5432 / medhistory`. Credentials hardcoded here (dev environment).

### Data Model Key Points

- **Roles:** `administradores`, `medicos` (split into `medicos_generales` + `medicos_especialistas`), `pacientes`, `dependientes`. `AuthController` detects role and returns it in the session.
- **Soft deletes everywhere:** Tables use `activo BOOLEAN` + `fecha_baja TIMESTAMP` — never DELETE rows. Foreign keys intentionally have no CASCADE to preserve history.
- **Audit log:** `AuditoriaDAO` / `auditoria_logs` table tracks all mutations for GDPR/LOPD compliance. Every write operation should log to audit.
- **Encrypted chat:** `ChatMedicosController` + `ChatMedicoDAO` use `backend/src/config/chat_crypto.php` for doctor-to-doctor messaging. `chat_key.local.txt` holds the key and is gitignored.

### Frontend Structure

- `frontend/static/css/tailwind.css` is the **compiled output** — never edit it directly. Edit `frontend/src/styles/tailwind.css` and rebuild.
- `dashboard.css` is manually authored and layered on top of Tailwind.
- JS modules are plain ES5-style scripts loaded via `<script>` tags, not ES modules with imports. They share globals set on `window` or defined at file scope.
- The doctor dashboard (`medico.html` + `dashboard-medico.js`) is the most complex page — it manages tabs for patients, consultations, citas, chat, and more.

### Session & Auth

`AuthController.php` handles login, sets `$_SESSION['usuario_id']`, `$_SESSION['rol']` (`paciente`/`medico`/`admin`/`dependiente`), and `$_SESSION['tipo_medico']` (`general`/`especialista`). All other controllers call `session_start()` and check session role before proceeding.
