# Diagrama de Flujo — Sistema Integral de Pago CPS

```mermaid
flowchart TD
    %% ─── Punto de entrada ───
    A([⏺ Inicio]) --> B[🔐 Login / Autenticación]
    B --> C{📊 Dashboard<br>según rol}

    %% ─── Tesorería ───
    subgraph TES [🏦 Módulo Tesorería]
        direction TB
        D[👤 CRUD Beneficiarios] --> E[📄 Crear Orden de Pago]
        E --> F[📨 Enviar a Financiera]
    end

    C -- Rol: Tesorería --> TES
    F --> G{❓ Financiera<br>¿Aprueba OP?}

    G -- Sí --> H[➡ Contabilidad]
    G -- No --> I([❌ Rechazado])

    %% ─── Contabilidad ───
    subgraph CON [📒 Módulo Contabilidad]
        direction TB
        J[🔍 Revisar Orden] --> K{❓ ¿Aprueba?}
        K -- Sí --> L[💰 Generar Cheque]
        K -- No --> I
        L --> M{❓ ¿Enviar a<br>Archivos?}
        M -- Sí --> N[📁 Enviar a Archivos]
        M -- No --> O[📤 Enviar a Presupuesto]
    end

    H --> CON
    N --> P[➡ Archivos]
    O --> Q[➡ Presupuesto]

    %% ─── Archivos ───
    subgraph ARC [📂 Módulo Archivos]
        direction TB
        R[🗂 Archivar documentos] --> S[📤 Derivar a Presupuesto]
    end

    P --> ARC

    %% ─── Presupuesto ───
    subgraph PRE [💰 Módulo Presupuesto]
        direction TB
        T{❓ ¿Aprueba<br>Cheque?}
        T -- Sí --> U[📤 Derivar a Financiera]
        T -- No --> I
    end

    S --> PRE
    Q --> PRE

    %% ─── Financiera - Cheque ───
    subgraph FIN2 [🏛 Financiera - Cheque]
        direction TB
        V{❓ ¿Aprueba<br>Cheque?}
        V -- Sí --> W[📤 Derivar a Administración]
        V -- No --> I
    end

    U --> FIN2

    %% ─── Administración ───
    subgraph ADM [📋 Módulo Administración]
        direction TB
        X{❓ ¿Aprueba<br>Cheque?}
        X -- Sí --> Y[📤 Derivar a Caja]
        X -- No --> I
    end

    W --> ADM

    %% ─── Caja ───
    subgraph CAJ [💵 Módulo Caja]
        direction TB
        Z[🤝 Entregar Cheque] --> AA{❓ ¿Cobrado?}
        AA -- Sí --> AB[✅ Cobrado]
        AA -- No --> AC[🔄 Revalidar]
        AC --> Z
    end

    Y --> CAJ
    AB --> AD([✅ Entregado])
    AD --> AE([🔒 Cerrado])

    %% ─── Subprocesos transversales ───
    subgraph TRANS [🔄 Subprocesos Transversales]
        direction TB
        AF[📜 Tracking / Historial]
        AG[📊 Reportes PDF y CSV]
        AH[🔍 Consulta Pública<br>de Cheques]
    end

    C --> AF
    C --> AG
    C -.-> AH

    AF -.->|Registra cada cambio de estado| TES
    AF -.->|Registra cada cambio de estado| CON
    AF -.->|Registra cada cambio de estado| ARC
    AF -.->|Registra cada cambio de estado| PRE
    AF -.->|Registra cada cambio de estado| FIN2
    AF -.->|Registra cada cambio de estado| ADM
    AF -.->|Registra cada cambio de estado| CAJ

    %% ─── Estilos ───
    classDef inicio fill:#095940,color:#fff,stroke:#064530,stroke-width:2px
    classDef proceso fill:#e8f5e9,stroke:#4caf50,stroke-width:1px
    classDef decision fill:#fff3e0,stroke:#ff9800,stroke-width:2px
    classDef terminal fill:#f44336,color:#fff,stroke:#b71c1c,stroke-width:2px
    classDef terminalOk fill:#095940,color:#fff,stroke:#064530,stroke-width:2px
    classDef subproceso fill:#e3f2fd,stroke:#1976d2,stroke-width:1px,stroke-dasharray: 5 5

    class A inicio
    class B,D,E,F,J,L,N,O,R,S,U,W,Y,Z,AB,AC,AD,AE proceso
    class C,G,K,M,T,V,X,AA decision
    class I terminal
    class AF,AG,AH subproceso
```

## Descripción del Flujo

### Flujo Principal de Aprobación

| Paso | Responsable | Acción |
|------|-------------|--------|
| 1 | Tesorería | Crea beneficiarios y órdenes de pago |
| 2 | Financiera | Aprueba o rechaza la orden de pago |
| 3 | Contabilidad | Aprueba, genera cheque, decide si envía a Archivos |
| 4 | Archivos | Archiva documentos y deriva a Presupuesto |
| 5 | Presupuesto | Aprueba o rechaza el cheque |
| 6 | Financiera | Aprueba o rechaza el cheque |
| 7 | Administración | Aprueba o rechaza el cheque |
| 8 | Caja | Entrega el cheque, registra cobro o revalida |
| 9 | — | Orden marcada como Entregada → Cerrada |

### Estados Posibles

| Estado | Descripción |
|--------|-------------|
| `pendiente_tesoreria` | Creada por Tesorería, esperando envío |
| `en_financiera` | En revisión de Financiera |
| `en_contabilidad` | En revisión de Contabilidad |
| `en_archivos` | En archivado de documentos |
| `en_presupuesto` | En revisión de Presupuesto |
| `en_financiera_cheque` | En revisión de cheque por Financiera |
| `en_administracion` | En revisión de Administración |
| `en_caja` | En proceso de entrega |
| `entregado` | Cheque entregado al beneficiario |
| `cerrado` | Proceso finalizado |
| `rechazado` | Rechazado en cualquier etapa (terminal) |

### Subprocesos Transversales

- **Tracking/Historial**: Registra cada cambio de estado con fecha, usuario, área origen/destino y comentarios.
- **Reportes**: Generación de reportes en PDF y CSV por rango de fechas, área, tipo, etc.
- **Consulta Pública**: Cualquier persona puede consultar el estado de un cheque por su número (ruta pública).

---

*Documento generado para el informe del Sistema Integral de Pago CPS — Caja Petrolera de Salud, Cochabamba, Bolivia.*
