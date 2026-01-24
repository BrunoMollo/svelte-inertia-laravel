# Crear usuario

El admin al crear un usuario debe tener dos formularios:

1. Crear con invitacion via mail
2. Crear con contraseña manual

El admin debe poder eleguir con un componeten tab (shadcn) el estado del modo seleccionado debe quedar como query param (para que al recargar se mantenga)

## Crear un usuario con mail de invitacion (camino feliz)

- supedamin crea un nuevo usuario (rol profesor) llenando le formulario de como invitacion por mail(con un mail dado)
- el mail que el superadmin completo recibe un mail de invitacion con un link
- el mail del usuarios e considera automaticamente como verficado
- el usuario entra al mail de invitacion y entra al link que contiene el mismo
- el mail que contiene le mismo le deja entrar (sin middelware de autenticacion ) y le pida la contraseña a definir (con confirmacion)
- una vez indicada la contraseña el usuario entra al sistema (ve el dashboard de profesor)

## Crear un usuario con psw manual (camino feliz)

- supedamin crea un nuevo usuario (rol profesor) llenando le formulario(con un mail dado)
- El sistema genera un contraseña segura aleatoria y se la muestra al admin una sola vez (da opcion de copiar con un clic)
- Luego otro usuario trata de loguearse con ese mail y contraseña
- Al entrar, le aparece una pantalla de que se le envio un mail de confirmacion de email
- El usuario entra al mail de confirmacion y confirma entrando en el link dado ()
- Al recargar la pagina antnerior, el usuario queda como verificado

(crear al menos un test completo para cada camino feliz)

Reglas de negocio:

- La contraseña del usuario debe tener al menos 8 caracteres (debe estar como una Rule)
- No puede haber mas de un usuario con el mismo mail
