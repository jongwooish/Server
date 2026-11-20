# Directiva de grupo

[한국어](README.ko.md) | [English](../README.md) | [日本語](README.ja.md) | [简体中文](README.zh-CN.md) | **Español** | [Français](README.fr.md)

![Windows Server](https://img.shields.io/badge/Windows_Server-Active_Directory-0078D4?logo=windows&logoColor=white)

La directiva de grupo (Group Policy), la segunda gran razón para usar AD. Explica qué puede hacer, cómo se estructura y dónde se puede vincular, GPC y GPT, la diferencia entre GPO y GPP, los intervalos de actualización, el orden de procesamiento y el filtrado, y las herramientas para comprobar los resultados.

## Contenido

- [1. ¿Qué es la directiva de grupo?](#1-qué-es-la-directiva-de-grupo)
- [2. Estructura y ámbito de vinculación](#2-estructura-y-ámbito-de-vinculación)
- [3. Cómo funciona la directiva de grupo](#3-cómo-funciona-la-directiva-de-grupo)
- [4. Directiva de grupo (GPO) vs preferencias (GPP)](#4-directiva-de-grupo-gpo-vs-preferencias-gpp)
- [5. Orden de procesamiento y filtrado](#5-orden-de-procesamiento-y-filtrado)
- [6. Comprobar resultados y plantillas administrativas](#6-comprobar-resultados-y-plantillas-administrativas)
- [Resumen](#resumen)

---

## 1. ¿Qué es la directiva de grupo?

Hay dos grandes razones para usar AD, y la directiva de grupo es una de ellas.

1. **Autenticación centralizada (SSO)**: un solo inicio de sesión lo resuelve todo.
2. **Administración centralizada (GP)**: la tecnología para administrar los clientes y servidores Windows de una empresa u organización.

### Ventajas de la directiva de grupo

- **Aplicación en bloque**: muchas configuraciones de Windows se aplican de forma centralizada de una sola vez.
- **Aplicación forzada**: las configuraciones que nunca deben cambiar se pueden bloquear desde el centro, lo que permite crear un **estándar de la empresa**.

### Qué puede hacer la directiva de grupo

1. **Configuración de seguridad**: aplica la configuración de seguridad de Windows.
2. **Configuración de aplicaciones**: distribuye de forma centralizada la configuración de Windows y de muchas otras aplicaciones.
3. **Configuración de red**: gestiona en bloque el firewall, la seguridad IP, las unidades de red asignadas, etc.
4. **Redirección de carpetas**: reúne las carpetas de perfil de usuario en un servidor para facilitar la copia de seguridad y la administración.

### Tres modos de configuración

| Modo | Significado |
| --- | --- |
| Habilitada (Enabled) | Aplica la configuración. |
| Deshabilitada (Disabled) | Aplica la configuración en sentido contrario (la desactiva). |
| No configurada (Not Configured) | Valor nulo (Null): no se decide nada. |

---

## 2. Estructura y ámbito de vinculación

### Configuración del equipo y del usuario

La directiva de grupo se divide en dos grandes bloques.

| Bloque | Destino | Cuándo se aplica |
| --- | --- | --- |
| Configuración del equipo | **Cuentas de equipo** en AD | Cuando el equipo **arranca** |
| Configuración del usuario | **Cuentas de usuario** en AD | Cuando el usuario **inicia sesión** |

### Ámbito de vinculación

El ámbito decide **dónde se vincula y se aplica** una directiva de grupo.

- **Dominio**: el ámbito básico de AD.
- **Unidad organizativa (OU)**: una unidad que delimita un ámbito dentro del dominio.
- **Sitio**: permite que la directiva siga la estructura de la red.

> **No se puede vincular directamente** a un solo usuario, grupo o equipo. Las directivas se vinculan a dominios, OU y sitios.

---

## 3. Cómo funciona la directiva de grupo

Lo que llamamos "directiva de grupo (GPO)" se compone en realidad de dos partes.

![What a GPO is made of](../images/ad-gpc-gpt.svg)

1. **Contenedor de directiva de grupo (GPC)**: se guarda **en AD** y vincula la directiva con el lugar donde se aplica.
2. **Plantilla de directiva de grupo (GPT)**: contiene **la configuración real** y se guarda en la carpeta **SYSVOL**, creada al instalar el DC.

---

## 4. Directiva de grupo (GPO) vs preferencias (GPP)

| Concepto | Directiva de grupo (GPO) | Preferencias de directiva de grupo (GPP) |
| --- | --- | --- |
| Obligatoriedad | Se aplica de forma estricta. (Strictly Enforced) | Se aplica, pero el usuario puede cambiarla. |
| ¿Puede cambiarla el usuario? | No: la opción aparece desactivada en la interfaz. | Sí, pero el valor deseado se vuelve a aplicar periódicamente. |
| Uso | Configuración que debe forzarse, como la seguridad y los estándares de la empresa | Áreas adicionales que las GPO no cubren, o configuraciones sencillas basadas en GUI |

En general, las **GPO** son mucho más eficaces para la configuración de seguridad y los estándares de la empresa que deben forzarse.

### Momento de actualización (Refresh)

Además de al arrancar y al iniciar sesión, las directivas también se actualizan **periódicamente**.

- **Intervalo predeterminado**: 90 minutos más un desfase aleatorio de ±30 minutos, por lo que se aplican en **60–120 minutos**.
- **Configuración de seguridad**: se vuelve a aplicar **cada 16 horas** aunque no haya cambios.
- **Forzar la actualización**: si no quieres esperar, aplícala al instante con **`gpupdate`**.

```cmd
gpupdate /force
```

---

## 5. Orden de procesamiento y filtrado

### Orden de procesamiento

Las directivas se aplican bajando por varios niveles, y **el valor aplicado en último lugar es el resultado final**.

![Group Policy processing order](../images/ad-gpo-order.svg)

1. Directiva de grupo local
2. Sitio
3. Dominio
4. OU

### Directivas de cuenta

Las directivas de cuenta, como la **directiva de contraseñas**, se aplican **solo a nivel de dominio**.

> Ejemplo: si el dominio fija una longitud mínima de contraseña de 8 caracteres, una OU que fije 3 seguirá teniendo **8**.

### Exigir y bloquear la herencia

Se pueden evitar conflictos entre directivas o fijar su prioridad.

- **Exigir (Enforce)**: una directiva superior (p. ej., del dominio) se **impone hacia abajo** y los niveles inferiores no pueden sobrescribirla.
- **Bloquear herencia (Block Inheritance)**: una OU hija **rechaza las directivas que vienen de arriba**. (No puede bloquear las directivas exigidas.)

### Filtrado de seguridad y filtrado WMI

- **Filtrado de seguridad**: ajusta los permisos para que una directiva excluya ciertos grupos o usuarios, o se aplique solo a los grupos que quieras.
- **Filtrado WMI**: aplica una directiva según **condiciones del equipo**, como la versión del sistema operativo.

---

## 6. Comprobar resultados y plantillas administrativas

### Comprobar el resultado

- **Conjunto resultante de directivas (RSoP)**: usa Resultados de directiva de grupo en la Consola de administración de directivas de grupo (**GPMC**) o **`rsop.msc`** para ver qué directivas se aplicaron finalmente.
- **`gpresult`**: se usa en el símbolo del sistema para ver qué directivas tiene actualmente el equipo.

```cmd
gpresult /r
```

### Plantillas administrativas

- **Donde se reúne la configuración básica de Windows**, como el Panel de control y los componentes de Windows.
- La mayoría de las configuraciones que quieras se hacen aquí.

---

## Resumen

- Las dos razones para usar AD son la **autenticación centralizada (SSO)** y la **administración centralizada (directiva de grupo)**. La directiva de grupo **aplica configuraciones en bloque** y las **impone**.
- Los modos son **Habilitada / Deshabilitada / No configurada**. **La configuración del equipo se aplica al arrancar**, **la del usuario al iniciar sesión**.
- Las directivas solo se pueden vincular a **dominios, OU y sitios**, no directamente a usuarios, grupos o equipos individuales.
- Una GPO se compone del **GPC** (en AD, información de vinculación) y la **GPT** (en SYSVOL, la configuración real). Usa **GPO** para imponer y **GPP** para valores predeterminados flexibles.
- La actualización es **90 ± 30 minutos**, la seguridad **cada 16 horas**, y **`gpupdate`** aplica al instante.
- El orden es **Local → Sitio → Dominio → OU**, y gana el último valor. **Las directivas de cuenta solo se aplican en el dominio.** Ajusta el ámbito con **Enforce**, **Block Inheritance** y el **filtrado de seguridad/WMI**.
- Comprueba los resultados con **RSoP (`rsop.msc`, GPMC)** y **`gpresult`**.
