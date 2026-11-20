# AD DS の構成要素と LDAP

[한국어](README.ko.md) | [English](../README.md) | **日本語** | [简体中文](README.zh-CN.md) | [Español](README.es.md) | [Français](README.fr.md)

![Windows Server](https://img.shields.io/badge/Windows_Server-Active_Directory-0078D4?logo=windows&logoColor=white)

AD が認証に使うプロトコル（Kerberos）と情報の照会に使うプロトコル（LDAP）、AD を構成する物理的・論理的な構成要素を整理します。続いて、ドメイン、OU、DN、フォレストの概念を図とともに見ていきます。

## 目次

- [1. 基本用語とプロトコル](#1-基本用語とプロトコル)
- [2. AD の構成要素](#2-ad-の構成要素)
- [3. AD DS ドメイン](#3-ad-ds-ドメイン)
- [4. OU と識別名](#4-ou-と識別名)
- [5. LDAP と照会ツール](#5-ldap-と照会ツール)
- [6. AD DS フォレスト](#6-ad-ds-フォレスト)
- [まとめ](#まとめ)

---

## 1. 基本用語とプロトコル

### 用語

| 英語 | 意味 |
| --- | --- |
| Attribute | 属性 |
| Object | オブジェクト |
| department | 部署名の属性 |
| displayName | 表示名の属性 |

### 認証は Kerberos、照会は LDAP

- Active Directory は認証に **Kerberos** プロトコルを使います。ポート番号は **88 番**です。
- AD は認証用だけでなく、**情報を持つデータベース**としても使えます。
- 何かを**照会（クエリ）** するときは **LDAP** プロトコルを使い、ポート番号は **389 番**です。
- つまり AD はデータベースであり、その情報で**認証**も行い、**クエリ**で情報を取り出すこともできます。

---

## 2. AD の構成要素

AD には、実際に目に見える**物理的な構成要素（Physical components）** と、概念的な**論理的な構成要素（Logical components）** があります。AD で認証を処理するサーバーを**ドメインコントローラー（Domain Controller、DC）** と呼びます。

![AD DS components](../images/ad-components.svg)

### 物理的な構成要素

- **Data store**: DC 上で目に見えるデータベースファイルです。
- **Domain controllers**: 認証を処理するサーバーです。
- **Global catalog server**: DC のうち特別な役割を持つサーバーを GC と呼びます。
- **RODC**: 読み取り専用ドメインコントローラーです。

### 論理的な構成要素

- **Partitions**: AD データベースは内部で用途別に分かれており、その一つひとつをパーティションと呼びます。
- **Schema**: オブジェクトと属性（Attribute）の定義です。
- **Domains**: 1 つの管理・認証の境界です。
- **Domain trees**: 複数のドメインがつながって形づくるツリーです。
- **Forests**: ドメインツリーが複数集まったものです。
- **Sites**: ネットワーク上の区分を表します。
- **OUs**: 1 つの境界の中を理由に応じて分けた組織単位です。

---

## 3. AD DS ドメイン

- ドメインは、**1 台のドメインコントローラーが認証できる最大の範囲**です。
- ドメインを作るには、**ドメインコントローラーが 1 台以上**必要です。
- ドメインコントローラーには、ドメインの情報が **Object（オブジェクト）単位**で保存されます。
- AD ではドメインコントローラーを **2 台以上**構成することを推奨しています。（例: 負荷分散、障害への備え）
- ドメイン管理者は、ドメイン内の**すべての権限**を持ちます。
- AD で**最も小さい境界の単位**がドメインです。

---

## 4. OU と識別名

### OU（Organizational Unit）

1 つのドメインの中を**論理的に分割する技術**を OU と言います。

![OUs inside a domain](../images/ad-ou-structure.svg)

- 管理のためにドメインを細分化する作業です。
- **グループポリシー（GPO）を配布する単位**にもなります。

### 識別名（Distinguished Name、DN）

AD の中で**オブジェクトを一意に指す名前**を識別名と言います。いちばん下のオブジェクトから上のドメインへの順に書きます。

- `OU=Sales,DC=KOALRA,DC=COM`: KOALRA.COM ドメインの Sales OU
- `CN=Koalra,OU=Marketing,DC=KOALRA,DC=COM`: Marketing OU の中の Koalra オブジェクト

---

## 5. LDAP と照会ツール

AD はデータベースです。**LDAP（Lightweight Directory Access Protocol）** は、ネットワーク上のユーザー、グループ、デバイスなどの情報を中央で効率よく管理・照会するためのディレクトリサービスプロトコルです。

AD は標準でクエリが可能で、次のツールで AD を照会できます。

- **Active Directory 管理ツール**（ユーザーとコンピューターなど）
- **LDP**（`ldp.exe`）
- **ADSI エディター**（`ADSIEDIT.MSC`）

---

## 6. AD DS フォレスト

AD ではドメインを複数作ることができ、それらのドメインを互いにつないだものを**フォレスト（Forest）** と言います。

![Domains, trees and a forest](../images/ad-forest.svg)

- フォレストは、**同じ Attribute と Object の構造を持つドメインの集合体**です。
- このように構造が同じであることを、**スキーマ（Schema）が同じ**と言います。
- 同じ名前空間でつながったドメインのまとまり（例: `koalra.com` の下の `sales.koalra.com`）が**ドメインツリー**で、ドメインツリーが複数集まるとフォレストになります。

---

## まとめ

- AD は **Kerberos（88 番）** で認証し、**LDAP（389 番）** で情報を照会するデータベースです。
- 物理的な構成要素は **Data store、DC、GC、RODC**、論理的な構成要素は**パーティション、スキーマ、ドメイン、ドメインツリー、フォレスト、サイト、OU** です。
- **ドメイン**は DC が認証できる最大の範囲であり、AD の最小の境界です。DC は 2 台以上が推奨です。
- **OU** はドメインの中を論理的に分けた単位で、**グループポリシーを適用する単位**です。
- **DN** は `CN=…,OU=…,DC=…` の形式でオブジェクトを一意に指します。
- **フォレスト**は同じスキーマを共有するドメイン（ツリー）の集まりです。
