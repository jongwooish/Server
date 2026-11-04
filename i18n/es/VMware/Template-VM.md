# VMware Template VM

[한국어](../../ko/VMware/Template-VM.md) | [English](../../../VMware/Template-VM.md) | [日本語](../../ja/VMware/Template-VM.md) | [简体中文](../../zh-CN/VMware/Template-VM.md) | **Español** | [Français](../../fr/VMware/Template-VM.md)

Cuando una práctica o una prueba de competición necesita muchas VM, instalar cada sistema operativo a mano lleva demasiado tiempo. En su lugar, instala bien una VM, conviértela en una **Template VM** y **clona** el resto a partir de ella.

Esta página explica cómo preparar la Template VM en VMware Workstation, cómo clonar a partir de ella y qué hay que corregir dentro de cada clon.

---

## 1. ¿Qué es una Template VM?

Una Template VM es la **VM base (padre)** a partir de la cual se crean todos los clones.

| Término | Significado |
| --- | --- |
| **Template VM** | La VM original que instalas una vez y guardas como base |
| **Snapshot** | Estado guardado de la VM en un momento concreto. Los clones se crean a partir de un snapshot |
| **Clone** | Una VM nueva copiada del snapshot de la plantilla |
| **Template mode** | Opción de VMware que protege la plantilla para que no se borre ni se estropee por accidente |

---

## 2. Preparar la Template VM

### Paso 0. Dejar lista la VM base

Antes de tomar el snapshot, mete en esta VM todo lo que deben compartir los clones.

- Instala el sistema operativo y los paquetes que siempre usas (`vim`, `ssh`, `curl` … )
- Aplica tu configuración base habitual (vimrc, ajustes de SSH, etc.)
- **No** pongas nada propio de cada máquina: nombre de host, IP fija, certificados, claves
- **Apaga la VM** (Power Off / Shut Down Guest)

> Toma el snapshot con la VM **apagada**. Los clones creados a partir de un snapshot apagado arrancan limpios, como una máquina recién encendida.

### Paso 1. Crear un snapshot llamado `Template`

**VM → Snapshot → Snapshot Manager → Take Snapshot**

- Nombre: `Template`
- Descripción (opcional): qué está instalado, la fecha, etc.

### Paso 2. Activar el Template mode

**VM → Settings → Options → Advanced → marca `Enable Template mode (to be used for cloning)`**

![Enable Template mode](../../../VMware/images/enable-template-mode.png)

**¿Por qué hacerlo?**
- El snapshot se conserva y **no se puede borrar por error**.
- La propia Template VM queda **protegida contra el borrado**.
- Es importante porque **los linked clones dependen del snapshot de la plantilla**. Si la plantilla o su snapshot desaparecen, todos los linked clones se rompen.

Con esto la Template VM está lista y clonar se vuelve rápido y seguro.

---

## 3. Clonar a partir de la plantilla

**Clic derecho en la Template VM → Manage → Clone**

1. **Clone from** → `An existing snapshot` → selecciona `Template`
2. **Clone type** → elige uno de los dos tipos de abajo
3. Escribe el nombre de la nueva VM (p. ej. `FW`, `DNS`, `WEB1`) y su ubicación → **Finish**

| | **Linked clone** | **Full clone** |
| --- | --- | --- |
| Cómo funciona | Comparte el disco de la plantilla y guarda solo los cambios | Copia el disco completo |
| Velocidad de creación | **Segundos** | Minutos (según el tamaño del disco) |
| Uso de disco | **Muy pequeño** | Igual que la plantilla |
| ¿Necesita la plantilla? | **Sí** — se rompe si la plantilla o el snapshot desaparecen | No — totalmente independiente |
| Ideal para | Prácticas, entrenamiento para competiciones, muchas VM de uso corto | VM que vas a conservar mucho tiempo o llevar a otro PC |

> Para prácticas y entrenamiento de competición, los **linked clones** suelen ser la mejor opción: rápidos y ligeros. Eso sí, no borres ni modifiques nunca la Template VM después.

---

## 4. Después de clonar: corregir lo que debe ser único

Un clon es una copia exacta, así que algunos valores son **idénticos** a los de la plantilla y los demás clones. Corrígelos en cada clon antes de usarlo.

### Linux (Debian / Ubuntu)

| Elemento | Por qué | Comando |
| --- | --- | --- |
| Hostname | Todos los clones tienen el nombre de la plantilla | `hostnamectl set-hostname WEB1` y edita la línea `127.0.1.1` de `/etc/hosts` |
| Dirección IP | Evitar conflictos de IP | Edita `/etc/network/interfaces` (Debian) o Netplan (Ubuntu) |
| machine-id | Con el mismo ID, el DHCP puede dar **la misma IP** a clones distintos | `rm /etc/machine-id && systemd-machine-id-setup` |
| Claves de host SSH | Todos los clones comparten las mismas claves | `rm /etc/ssh/ssh_host_* && dpkg-reconfigure openssh-server` |

```bash
# Ejemplo: ejecutar dentro de un clon nuevo y reiniciar
hostnamectl set-hostname WEB1
sed -i 's/^127.0.1.1.*/127.0.1.1\tWEB1/' /etc/hosts
rm -f /etc/machine-id && systemd-machine-id-setup
rm -f /etc/ssh/ssh_host_* && dpkg-reconfigure openssh-server
reboot
```

### Windows Server

Los clones de Windows comparten el mismo **SID**, lo que da problemas con Active Directory (unión al dominio, etc.).
Ejecuta **Sysprep** en la plantilla **antes** de tomar el snapshot `Template`.

```powershell
C:\Windows\System32\Sysprep\sysprep.exe /generalize /oobe /shutdown
```

- `/generalize` : elimina la información propia de la máquina, como el SID
- `/oobe` : muestra la pantalla de configuración inicial en el siguiente arranque
- `/shutdown` : apaga al terminar → después toma el snapshot

### Adaptador de red (dirección MAC)

VMware suele asignar automáticamente una **dirección MAC nueva** a cada clon.
Si aun así dos clones tienen la misma MAC, ve a **VM → Settings → Network Adapter → Advanced → Generate**.

---

## 5. Resumen

1. Instala la VM base → apágala
2. Snapshot Manager → crea un snapshot llamado `Template`
3. Settings → Options → Advanced → **Enable Template mode**
4. Manage → Clone → desde el snapshot `Template` → **Linked clone** (rápido) o **Full clone** (independiente)
5. En cada clon: cambia **hostname, IP, machine-id y claves de host SSH** (Windows: **Sysprep** antes del snapshot)
