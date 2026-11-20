# 域控制器、架构与 FSMO

[한국어](README.ko.md) | [English](../README.md) | [日本語](README.ja.md) | **简体中文** | [Español](README.es.md) | [Français](README.fr.md)

![Windows Server](https://img.shields.io/badge/Windows_Server-Active_Directory-0078D4?logo=windows&logoColor=white)

深入了解提供 AD 服务的域控制器（DC）。本文整理架构与对象结构、构成 DC 的数据库、身份验证服务和组策略文件、数据库分区、全局编录，以及林范围的 FSMO 角色。

## 目录

- [1. 什么是域控制器？](#1-什么是域控制器)
- [2. 架构与对象结构](#2-架构与对象结构)
- [3. DC 的组成部分](#3-dc-的组成部分)
- [4. AD 数据库分区](#4-ad-数据库分区)
- [5. 全局编录（GC）](#5-全局编录gc)
- [6. FSMO 角色（操作主机）](#6-fsmo-角色操作主机)
- [总结](#总结)

---

## 1. 什么是域控制器？

- 提供 AD 一切服务的计算机称为 **DC（Domain Controller）**。在 DC 上能看到的文件实际上只有**数据库**。
- 处理身份验证的协议是 **Kerberos**，处理它的服务称为 **KDC 服务**。
- 域控制器应当有**两台以上**。

---

## 2. 架构与对象结构

Active Directory 以称为**架构（Schema）** 的骨架为基础构建。

- **对象（Object）与属性（Attribute）**：AD 由对象构成，每个对象都有属性。
- **架构**：这些对象和属性的基本骨架，即**模板的设计定义**，称为架构。
- **结构一致**：整个**林使用同一个架构**。
- **AD 结构的起点与终点**：AD 的所有结构都始于架构，也终于架构。

---

## 3. DC 的组成部分

域控制器是提供 AD 服务的服务器，包含用于身份验证的服务和数据库。

![Inside a domain controller](../images/ad-dc-components.svg)

| 组成部分 | 内容 |
| --- | --- |
| AD 数据库文件 | AD 数据以 `NTDS.dit` 文件的形式保存在 `C:\Windows\NTDS` 文件夹下。 |
| 身份验证服务 | 使用 Kerberos 身份验证协议，处理它的服务是 **KDC**（Key Distribution Center，密钥分发中心）。 |
| 组策略文件 | 用于处理组策略的文件保存在 **SYSVOL** 文件夹下。 |

---

## 4. AD 数据库分区

AD 数据库（`NTDS.dit`）被分成多个部分保存，每个分区的**复制范围**不同。

| 分区 | 保存内容 | 范围 |
| --- | --- | --- |
| Schema | 对象和属性的基本结构定义 | 整个林 |
| Configuration | 在整个林中必须保持一致的配置信息 | 整个林 |
| Domain | 用户、组、计算机等该域的具体信息 | 该域 |
| Application | 为使用 AD 的程序（例如 DNS）分配的区域 | 指定的 DC |

---

## 5. 全局编录（GC）

GC 是负责**跨整个林的搜索和通讯簿查询**的服务器。

![Global catalog and FSMO roles](../images/ad-gc-fsmo.svg)

- 用于在同一个林中查找**其他域的账户**，或搜索整个林的信息。
- GC 查询使用的不是普通 LDAP 端口 389，而是 **3268** 端口。
- 为了避免不必要的网络流量，建议**每个站点至少部署一台 GC**。

---

## 6. FSMO 角色（操作主机）

AD 采用**多主机复制**方式，但对于绝不能发生复制冲突的重要操作，会指定为**单主机（Single Master）** 来管理。这些角色称为**操作主机（FSMO，Flexible Single Master Operations）**。

### 林范围的操作主机

每个林中**只有一台 DC** 承担这些角色。

- **架构主机（Schema Master）**：负责整个林架构的更改、修改和扩展。
- **域命名主机（Domain Naming Master）**：管理林中的域结构。（添加、删除域）

---

## 总结

- **DC** 是提供 AD 的服务器，Kerberos 身份验证由 **KDC 服务**处理。DC 应保持两台以上。
- **架构**是对象和属性的设计定义，**整个林使用同一个架构**。
- DC 的核心是 **`C:\Windows\NTDS\NTDS.dit`**（数据库）、**KDC**（身份验证）和 **SYSVOL**（组策略文件）。
- 数据库分区分为 **Schema 和 Configuration**（整个林）、**Domain**（域）以及 **Application**（例如 DNS）。
- **GC** 负责整个林的搜索，使用 **3268** 端口；每个站点至少部署一台。
- 不能冲突的操作作为 **FSMO** 角色交给一台 DC。林范围的角色是**架构主机**和**域命名主机**。
