# Rsync al servidor (nota)

Al sincronizar con `rsync`, **no** uses `--exclude='vendor'` sin anclar: excluye cualquier carpeta `vendor`, incluida `resources/views/vendor` (vistas publicadas de correo).

Usar exclusión solo en la raíz del proyecto:

```bash
--exclude='.git/' \
--exclude='/node_modules/' \
--exclude='/vendor/' \
```
