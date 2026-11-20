# Zonas DNS, herramientas y AGDLP

[한국어](README.ko.md) | [English](../README.md) | [日本語](README.ja.md) | [简体中文](README.zh-CN.md) | **Español** | [Français](README.fr.md)

![Windows Server](https://img.shields.io/badge/Windows_Server-Active_Directory-0078D4?logo=windows&logoColor=white)

Un repaso de las zonas DNS, los registros y las actualizaciones dinámicas —el núcleo de AD— y cómo instalar un controlador de dominio adicional (incluido Server Core). Después, las herramientas de administración de AD y la papelera de reciclaje, los tipos y ámbitos de grupos, y la estrategia de grupos AGDLP.

## Contenido

- [1. Zonas DNS y registros principales](#1-zonas-dns-y-registros-principales)
- [2. Agregar otro controlador de dominio](#2-agregar-otro-controlador-de-dominio)
- [3. Herramientas de administración y papelera de reciclaje de AD](#3-herramientas-de-administración-y-papelera-de-reciclaje-de-ad)
- [4. Tipos y ámbitos de grupos](#4-tipos-y-ámbitos-de-grupos)
- [5. La estrategia de grupos AGDLP](#5-la-estrategia-de-grupos-agdlp)
- [Resumen](#resumen)

---

## 1. Zonas DNS y registros principales

DNS es fundamental para AD, así que hay que entenderlo bien. Un servidor DNS gestiona los nombres creando **zonas (Zone)**.

### Búsqueda directa e inversa

| Búsqueda | Qué hace | Ejemplo |
| --- | --- | --- |
| Búsqueda directa | Nombre de dominio → dirección IP | `www.korea.com` → `192.168.0.1` |
| Búsqueda inversa | Dirección IP → nombre de dominio | `192.168.0.1` → `www.korea.com` |

### Tipos de zonas DNS

![DNS zone types](../images/ad-dns-zones.svg)

1. **Zona principal (Primary Zone)**: la zona principal que convierte nombres en IP. Se puede leer y escribir.
2. **Zona secundaria (Secondary Zone)**: una **copia de respaldo de solo lectura** de la zona principal.
3. **Zona de código auxiliar (Stub Zone)**: contiene **solo los registros NS** que indican dónde se sirve la zona y los **registros A** de esos servidores.

### Registros DNS principales

| Registro | Función |
| --- | --- |
| A | El registro más básico: asigna un nombre a una dirección IP. |
| CNAME | Un **alias** que apunta a otro nombre (un registro A). |
| MX | Indica la dirección del **servidor de correo** del dominio. |
| PTR | Se crea en una zona de búsqueda inversa para asignar una IP a un nombre. |

### Actualización dinámica (Dynamic Update)

Función con la que los clientes **registran por sí mismos su nombre de host en DNS**.

- Apenas se usa en un DNS normal, pero AD usa **actualizaciones dinámicas seguras**, que aplican seguridad cuando los clientes se registran.
- Las **actualizaciones dinámicas no seguras** pueden acumular datos basura, así que es mejor no usarlas.

---

## 2. Agregar otro controlador de dominio

Con un solo DC, la autenticación se detiene cuando ese DC cae. Por eso, en la práctica se recomiendan **dos o más DC**.

### Usar Server Core

- Un controlador de dominio de AD también se puede instalar en **Windows Server Core**, que es pequeño y necesita menos parches.
- Server Core no tiene herramientas gráficas, así que todo se hace con **PowerShell** o comandos. Usa **`sconfig`** para la configuración básica.
- Un servidor Core también se puede administrar de forma remota desde el **Administrador del servidor** de un servidor con GUI.

```powershell
# Instalar el rol AD DS
Install-WindowsFeature -Name AD-Domain-Services -IncludeManagementTools

# Agregar como DC a un dominio existente (promover)
Install-ADDomainController -DomainName "korea.com" -InstallDns
```

> **Consejo**: tanto con GUI como con CLI, antes de promover a DC configura el **DNS para que apunte a un DC existente**.

---

## 3. Herramientas de administración y papelera de reciclaje de AD

### Herramientas de administración

Hay dos herramientas para crear usuarios y grupos.

1. **Usuarios y equipos de Active Directory (ADUC)**: la herramienta de siempre.
2. **Centro de administración de Active Directory (ADAC)**: la herramienta más nueva. **Muestra el comando de PowerShell** de cada acción, lo que resulta útil para escribir scripts después.

### Papelera de reciclaje de AD

- Antes, un objeto de AD borrado por error **solo se podía recuperar desde una copia de seguridad**.
- Desde Windows Server 2008 R2, al habilitar la **papelera de reciclaje de AD** se pueden restaurar fácilmente usuarios o grupos eliminados.

---

## 4. Tipos y ámbitos de grupos

Usar bien AD significa usar bien los grupos. Si pones a los usuarios en grupos y das los derechos a los grupos, todo se gestiona de forma centralizada.

### Tipos de grupos

| Tipo | Uso |
| --- | --- |
| Grupo de distribución | Una lista para enviar correo. **No tiene identificador de seguridad (SID)**, así que no se le pueden dar permisos. |
| Grupo de seguridad | Se usa para **conceder permisos**. También se puede usar para correo. |

### Ámbitos de grupos

| Ámbito | Uso |
| --- | --- |
| Grupo global | Para **agrupar personas**. |
| Grupo local de dominio | Para **conceder permisos**. |
| Grupo universal | Abarca **todo el bosque**. Útil para agrupar grupos globales cuando hay varios dominios. |

---

## 5. La estrategia de grupos AGDLP

AGDLP no concede permisos directamente a los usuarios, sino **paso a paso a través de grupos**. Permite que toda la gestión de permisos se haga en AD, y es una de las razones principales para usar AD.

![AGDLP](../images/ad-agdlp.svg)

- **A** - Account: crea las cuentas de usuario.
- **G** - Global Group: mete a los usuarios en un grupo global.
- **DL** - Domain Local Group: mete el grupo global en un grupo local de dominio.
- **P** - Permission: concede el permiso al grupo local de dominio.

---

## Resumen

- Las zonas DNS son **principal** (lectura/escritura), **secundaria** (respaldo de solo lectura) y **de código auxiliar** (solo NS + A).
- Registros principales: **A** (nombre→IP), **CNAME** (alias), **MX** (servidor de correo), **PTR** (IP→nombre). AD usa **actualizaciones dinámicas seguras**.
- Mantén **dos o más** DC. En Server Core usa `sconfig`, `Install-WindowsFeature` e `Install-ADDomainController`, y **apunta el DNS a un DC existente** antes de promover.
- Las herramientas son **ADUC** y **ADAC**; ADAC muestra los comandos de PowerShell. Al activar la **papelera de reciclaje de AD**, los objetos eliminados se restauran fácilmente.
- Los tipos de grupos son **distribución** (sin SID) y **seguridad**; los ámbitos son **global** (personas), **local de dominio** (permisos) y **universal** (todo el bosque).
- **AGDLP**: cuenta → grupo global → grupo local de dominio → permiso.
