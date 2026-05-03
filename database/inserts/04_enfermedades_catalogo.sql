-- =============================================================================
-- CATÁLOGO DE ENFERMEDADES (100 enfermedades comunes)
-- Para usar en antecedentes familiares y perfiles de salud
-- =============================================================================

INSERT INTO enfermedades_catalogo (nombre_patologia) VALUES
    -- Cardiovasculares
    ('Hipertensión arterial'),
    ('Infarto de miocardio'),
    ('Arritmia cardíaca'),
    ('Angina de pecho'),
    ('Insuficiencia cardíaca'),
    ('Trombosis venosa'),
    ('Enfermedad cerebrovascular'),
    ('Ictus'),

    -- Endocrinas/Metabólicas
    ('Diabetes tipo 1'),
    ('Diabetes tipo 2'),
    ('Hipotiroidismo'),
    ('Hipertiroidismo'),
    ('Obesidad'),
    ('Dislipidemia'),
    ('Síndrome metabólico'),

    -- Respiratorias
    ('Asma'),
    ('EPOC'),
    ('Bronquitis crónica'),
    ('Enfisema'),
    ('Apnea del sueño'),
    ('Fibrosis pulmonar'),
    ('Tuberculosis'),

    -- Digestivas
    ('Úlcera péptica'),
    ('Gastritis'),
    ('Enfermedad de Crohn'),
    ('Colitis ulcerosa'),
    ('Síndrome del intestino irritable'),
    ('Cirrosis hepática'),
    ('Hepatitis B'),
    ('Hepatitis C'),
    ('Pancreatitis'),

    -- Renales
    ('Insuficiencia renal crónica'),
    ('Cálculos renales'),
    ('Nefritis'),
    ('Pielonefritis'),

    -- Neurológicas
    ('Alzheimer'),
    ('Parkinson'),
    ('Epilepsia'),
    ('Migraña'),
    ('Accidente cerebrovascular'),
    ('Esclerosis múltiple'),
    ('Distrofia muscular'),
    ('Miastenia gravis'),

    -- Oncológicas
    ('Cáncer de mama'),
    ('Cáncer de próstata'),
    ('Cáncer de pulmón'),
    ('Cáncer de colon'),
    ('Cáncer gástrico'),
    ('Melanoma'),
    ('Leucemia'),
    ('Linfoma'),

    -- Reumatológicas
    ('Artritis reumatoide'),
    ('Artrosis'),
    ('Lupus eritematoso sistémico'),
    ('Espondilitis anquilosante'),
    ('Gota'),
    ('Fibromialgia'),

    -- Hematológicas
    ('Anemia'),
    ('Hemofilia'),
    ('Talasemia'),
    ('Trombocitopenia'),

    -- Dermatológicas
    ('Psoriasis'),
    ('Dermatitis atópica'),
    ('Vitíligo'),
    ('Urticaria'),

    -- Psiquiátricas
    ('Depresión'),
    ('Ansiedad'),
    ('Trastorno bipolar'),
    ('Esquizofrenia'),
    ('Trastorno obsesivo compulsivo'),

    -- Infecciosas
    ('VIH'),
    ('COVID-19'),
    ('Influenza'),
    ('Neumonía'),
    ('Infección urinaria'),

    -- Oftalmológicas
    ('Miopía'),
    ('Hipermetropía'),
    ('Astigmatismo'),
    ('Cataratas'),
    ('Glaucoma'),
    ('Degeneración macular'),

    -- Otorrinolaringológicas
    ('Sordera'),
    ('Sinusitis'),
    ('Otitis'),
    ('Faringitis'),

    -- Óseas/Articulares
    ('Osteoporosis'),
    ('Fractura ósea'),
    ('Hernia discal'),
    ('Lordosis'),
    ('Cifosis'),

    -- Otras
    ('Alergia'),
    ('Asma alérgica'),
    ('Intolerancia a la lactosa'),
    ('Celiaquía'),
    ('Enfermedad de Graves'),
    ('Síndrome de Down'),
    ('Autismo');

COMMIT;
