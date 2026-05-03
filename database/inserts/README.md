# Scripts de Inserts Masivos - MedHistory

Esta carpeta contiene scripts SQL para poblar la base de datos con datos de prueba organizados en fases.

## Estructura de Archivos

- `00_clean_database.sql` — Limpia completamente la base de datos (TRUNCATE de todas las tablas)
- `01_administradoras.sql` — Inserta 2 administradoras
- `02_medicos.sql` — Inserta 50 médicos (15 generales + 10 pediatras + 25 especialistas)
- `03_pacientes.sql` — Pacientes (próximo)
- `04_consultas.sql` — Consultas (próximo)

## Ejecución

### Opción 1: Script Maestro (Recomendado)
```bash
psql -U postgres -d medhistory -f inserts_all.sql
```

### Opción 2: Por fases individuales
```bash
# Fase 0: Limpieza
psql -U postgres -d medhistory -f 00_clean_database.sql

# Fase 1: Administradoras
psql -U postgres -d medhistory -f 01_administradoras.sql

# Fase 2: Médicos
psql -U postgres -d medhistory -f 02_medicos.sql
```

## Datos Insertados

### Administradoras (2)
- Claudia Mateo (claudia@clinica.com)
- Yousra Jebari (yousra@clinica.com)

Contraseña: `test123`

### Médicos (50)

#### Generales (15)
Médicos de cabecera para pacientes adultos.

#### Especialistas - Pediatría (10)
Médicos especializados en atención pediátrica.

#### Especialistas - Otras Especialidades (25)
Cardiología, Dermatología, Neurología, Oftalmología, Otorrinolaringología, Traumatología, Cirugía General, Ginecología, Oncología, Neumología, Gastroenterología, Endocrinología, Reumatología, Nefrología, Hematología, Psiquiatría, Urología, Radiología, Anestesiología, Medicina Intensiva, Fisioterapia, Nutrición, Oftalmología, Estética, Medicina Deportiva.

**Contraseña para todos:** `test123`

**Email patrón:** `nombre.apellido@clinica.com`

**Número de colegiado:** `CMED001` a `CMED050`

## Próximos Pasos

Después de ejecutar los scripts de médicos y administradoras, se pueden insertar:
1. Pacientes (adultos y dependientes/menores)
2. Consultas
3. Antecedentes familiares
4. Perfiles de salud
5. Otros datos complementarios

## Contraseña Maestra

Todos los usuarios utilizan el hash bcrypt:
```
$2y$10$31kbV2ggSTYTzaCKwt6IIuZOmvKcG4BkQpB2lZn47V.wDB/krms2i
```

Que corresponde a la contraseña: `test123`
