import sys

html_file = r'c:\web\medHistory\frontend\static\medico.html'

with open(html_file, 'r', encoding='utf-8') as f:
    content = f.read()

old_line = '    <script src="js/dashboard-medico.js?v=20260423-13"></script>'
new_lines = '    <script src="js/badge-styles.js"></script>\n    <script src="js/dashboard-medico.js?v=20260423-13"></script>'

content = content.replace(old_line, new_lines)

with open(html_file, 'w', encoding='utf-8') as f:
    f.write(content)

print('✓ badge-styles.js agregado a medico.html')
