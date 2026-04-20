Construcción automática de CSS (Tailwind)

Este proyecto genera `static/css/tailwind.css` a partir de `src/styles/tailwind.css` usando PostCSS + Tailwind.

Automatización:
- Los scripts `postinstall`, `prepare` y `predeploy` en `package.json` ejecutan `npm run build:css`.

Qué significa: si ejecutas `npm install` dentro de `frontend`, el CSS se compilará automáticamente y no deberías hacer pasos manuales adicionales.

Comandos manuales (si los necesitas):

```powershell
cd frontend
npm install
npm run build:css
```
