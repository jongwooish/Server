# Guía de instalación de una VM Debian

[한국어](README.ko.md) | [English](../README.md) | [日本語](README.ja.md) | [简体中文](README.zh-CN.md) | **Español** | [Français](README.fr.md)

![Debian](https://img.shields.io/badge/Debian-12-A81D33?logo=debian&logoColor=white) ![VMware Workstation Pro](https://img.shields.io/badge/VMware_Workstation_Pro-17-607078?logo=vmware&logoColor=white)

Cómo crear una VM nueva en VMware Workstation Pro e instalar Debian con el instalador en modo texto. Esta VM se usa después como **Debian CLI VM**, la base de los clones posteriores.

## Contenido

- [1. Crear la VM](#1-crear-la-vm)
- [2. Iniciar el instalador y elegir la región](#2-iniciar-el-instalador-y-elegir-la-región)
- [3. Red y nombre de host](#3-red-y-nombre-de-host)
- [4. Cuentas y contraseñas](#4-cuentas-y-contraseñas)
- [5. Particionado del disco](#5-particionado-del-disco)
- [6. Selección de software y GRUB](#6-selección-de-software-y-grub)
- [Resumen](#resumen)

---

## 1. Crear la VM

Abre VMware y pulsa **Ctrl + N** para crear una VM nueva.

![New Virtual Machine Wizard](../images/01-new-vm-wizard.png)

Selecciona el **archivo ISO** necesario para arrancar.

![Select the ISO](../images/02-select-iso.png)

![Browse for the ISO](../images/03-browse-iso.png)

Pulsa **Next** y deja los valores por defecto hasta la siguiente pantalla.

![ISO selected](../images/04-iso-selected.png)

![Customize Hardware](../images/05-ready-customize.png)

En **Customize Hardware**, configura el **Network Adapter** como **Host-only**.

![Network Adapter: Host-only](../images/06-network-host-only.png)

No es obligatorio, pero si quieres puedes quitar el **USB Controller** y la **Sound Card**.

![Remove USB and Sound Card](../images/07-remove-usb-sound.png)

Pulsa **Finish** y la VM se enciende automáticamente.

![Finish](../images/08-finish.png)

---

## 2. Iniciar el instalador y elegir la región

Pulsa **Ctrl + G** para enviar la entrada a la VM y usa las **flechas, la barra espaciadora y Enter** para elegir.

![Boot menu](../images/09-boot-menu.png)

> Forma rápida: **English** Enter → **other** Enter → **aaa** Enter → **kkk** Enter

![Boot menu](../images/10-boot-menu-language.png)

![Select other](../images/11-select-other.png)

Busca el continente de tu país. Para Corea, selecciona **Asia**.

![Select Asia](../images/12-select-asia.png)

Baja un poco y selecciona **Korea, Republic of (South Korea)**, no North Korea.

![Select Korea](../images/13-select-korea.png)

En las siguientes pantallas de locale, elige como se muestra.

![Locale](../images/14-locale.png)

![Locale default](../images/15-locale-default.png)

---

## 3. Red y nombre de host

Cuando aparezca la pantalla de configuración de red (DHCP), pulsa **Cancelar (Cancel)**.

![Cancel DHCP](../images/16-dhcp-cancel.png)

![Network autoconfiguration failed](../images/17-network-failed.png)

Selecciona la última opción para **no configurar la red ahora**.

![Do not configure the network now](../images/18-no-network.png)

Para el nombre de host (nombre de administrador), usa el de la hoja de la tarea si lo indica; si no, escribe cualquiera (p. ej. `root`).

![Hostname](../images/19-hostname.png)

---

## 4. Cuentas y contraseñas

Para la contraseña de root, usa la de la hoja de la tarea si la indica; si no, elige una. Marca **Show Password in Clear** para comprobar que la has escrito bien.

![Root password](../images/20-root-password.png)

Escribe la misma contraseña otra vez para confirmarla.

![Root password verify](../images/21-root-password-verify.png)

Para el nombre del usuario, usa el de la hoja de la tarea si lo indica; si no, escribe cualquiera (p. ej. `sysop`).

![Full name](../images/22-full-name.png)

Escribe el nombre de la cuenta igual que el nombre de arriba.

![Username](../images/23-username.png)

Lo más cómodo es usar para el usuario la misma contraseña que para root, salvo que la hoja de la tarea indique otra. Aquí también marca **Show Password in Clear** para comprobarla.

![User password](../images/24-user-password.png)

Escribe la misma contraseña en el campo de confirmación.

![User password verify](../images/25-user-password-verify.png)

> La contraseña real visible en las capturas se ha ocultado como `********`.

---

## 5. Particionado del disco

Cuando pregunte cómo particionar el disco, selecciona la primera opción, **Guided - use entire disk**.

![Guided - use entire disk](../images/26-guided-entire-disk.png)

![Select disk](../images/27-select-disk.png)

![Partitioning scheme](../images/28-partition-scheme.png)

En esta pantalla, selecciona la última opción, **Finish partitioning and write changes to disk**.

![Finish partitioning](../images/29-finish-partitioning.png)

Cuando pregunte si quieres escribir los cambios de particiones y formatear, **selecciona siempre Yes**. Si eliges No, puede que tengas que empezar la configuración de nuevo.

![Write changes: Yes](../images/30-write-changes-yes.png)

---

## 6. Selección de software y GRUB

Esta pantalla pregunta si quieres enviar de forma anónima a los desarrolladores información sobre los paquetes que más usas. Selecciona **No**.

![Popularity contest: No](../images/31-popularity-no.png)

**Desmarca todo excepto la última casilla (standard system utilities).** Así se instala un sistema CLI sin escritorio.

![Software selection](../images/32-software-selection.png)

La pregunta es "¿Instalar el cargador de arranque GRUB en el disco principal?". Selecciona **Yes**.

![Install GRUB: Yes](../images/33-grub-yes.png)

Esta pantalla pregunta dónde instalar el cargador de arranque GRUB. Selecciona **el disco de abajo (/dev/sda)**.

![GRUB device](../images/34-grub-device.png)

La instalación ha terminado y el sistema se reiniciará. Pulsa **Continue** y la instalación de Linux habrá terminado. ¡Buen trabajo!

![Installation complete](../images/35-install-complete.png)

---

## Resumen

- Crea la VM con **Ctrl + N**, selecciona la ISO y cambia el **Network Adapter a Host-only** (quitar USB y Sound Card es opcional).
- Maneja el instalador con **Ctrl + G** y luego flechas, barra espaciadora y Enter. La región es **Asia → Korea**.
- **Cancela** la configuración DHCP y **configura la red más tarde**.
- Usa los **valores de la hoja de la tarea** para el nombre de host, el usuario y las contraseñas. Compruébalos con Show Password in Clear.
- Particionado: **Guided - use entire disk → Finish → Yes**.
- Deja **solo standard system utilities** e instala GRUB con **Yes → /dev/sda**.
