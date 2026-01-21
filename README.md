# 💰 Vibe Financial Tracker

A full-stack financial tracking application with comprehensive monitoring stack, built with modern web technologies.

![Vue.js](https://img.shields.io/badge/Vue.js-3.x-4FC08D?logo=vue.js)
![PHP](https://img.shields.io/badge/PHP-8.2--FPM-777BB4?logo=php)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?logo=mysql)
![Docker](https://img.shields.io/badge/Docker-Compose-2496ED?logo=docker)
![Prometheus](https://img.shields.io/badge/Prometheus-Monitoring-E6522C?logo=prometheus)
![Grafana](https://img.shields.io/badge/Grafana-Dashboard-F46800?logo=grafana)

---

## 📋 Table of Contents

- [Features](#-features)
- [Tech Stack](#-tech-stack)
- [Architecture](#-architecture)
- [Quick Start](#-quick-start)
- [Access Points](#-access-points)
- [Monitoring Dashboard](#-monitoring-dashboard)
- [Load Testing](#-load-testing)
- [Project Structure](#-project-structure)

---

## ✨ Features

- **User Authentication** - Register & login with secure password hashing
- **Transaction Management** - Full CRUD for income/expense tracking
- **Dashboard** - Visual summary with charts (Chart.js)
- **Real-time Monitoring** - Prometheus + Grafana integration
- **Load Testing** - k6 scripts for performance testing
- **Containerized** - Full Docker Compose setup

---

## 🛠 Tech Stack

| Layer | Technology |
|-------|------------|
| **Frontend** | Vue.js 3, Vite, Chart.js, Axios |
| **Backend** | PHP 8.2-FPM, Nginx |
| **Database** | MySQL 8.0 |
| **Cache** | Redis (metrics storage) |
| **Monitoring** | Prometheus, Grafana, Node Exporter, MySQL Exporter |
| **Load Testing** | k6 |
| **Container** | Docker, Docker Compose |

---

## 🏗 Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                         Docker Network                           │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌──────────┐    ┌─────────────┐    ┌──────────┐                │
│  │ Frontend │───▶│ Backend     │───▶│ MySQL    │                │
│  │ (Vue.js) │    │ (PHP-FPM +  │    │ Database │                │
│  │ :80      │    │  Nginx)     │    │          │                │
│  └──────────┘    │ :8000       │    └──────────┘                │
│                  └──────┬──────┘                                 │
│                         │                                        │
│                         ▼                                        │
│                  ┌──────────┐                                    │
│                  │  Redis   │                                    │
│                  │ (Metrics)│                                    │
│                  └──────────┘                                    │
│                                                                  │
│  ┌────────────────────────────────────────────────────────────┐ │
│  │                    Monitoring Stack                         │ │
│  │  ┌────────────┐  ┌───────────────┐  ┌──────────────────┐   │ │
│  │  │ Prometheus │◀─│ Node Exporter │  │ MySQL Exporter   │   │ │
│  │  │ :9090      │  └───────────────┘  └──────────────────┘   │ │
│  │  └─────┬──────┘                                             │ │
│  │        │                                                    │ │
│  │        ▼                                                    │ │
│  │  ┌────────────┐                                             │ │
│  │  │  Grafana   │                                             │ │
│  │  │  :3000     │                                             │ │
│  │  └────────────┘                                             │ │
│  └────────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🚀 Quick Start

### Prerequisites

- Docker & Docker Compose
- k6 (for load testing) - [Installation Guide](https://k6.io/docs/getting-started/installation/)

### Run the Application

```bash
# Clone the repository
git clone <repository-url>
cd <project-folder>

# Start all services
docker-compose up -d --build

# Wait for services to be ready (~1-2 minutes for first build)
docker-compose ps
```

### Stop the Application

```bash
docker-compose down

# To also remove volumes (database data)
docker-compose down -v
```

---

## 🔗 Access Points

| Service | URL | Credentials |
|---------|-----|-------------|
| **Web Application** | http://localhost | Register new account |
| **Grafana Dashboard** | http://localhost:3000 | admin / admin |
| **Prometheus** | http://localhost:9090 | - |
| **Backend API** | http://localhost:8000/api | - |

---

## 📊 Monitoring Dashboard

The Grafana dashboard "**VibeTracker Metrics**" displays:

| Panel | Metric | Description |
|-------|--------|-------------|
| **Services Health** | `up` | UP/DOWN status of all services |
| **RPS** | `rate(http_requests_total[1m])` | Requests per second |
| **P95 Response Time** | `histogram_quantile(0.95, ...)` | 95th percentile latency |
| **CPU Usage** | `node_cpu_seconds_total` | System CPU usage |
| **Memory Usage** | `node_memory_*` | System memory consumption |
| **Error Rate** | `http_requests_total{status=~"4..\|5.."}` | Percentage of 4xx/5xx errors |
| **Database Latency** | `http_request_duration_seconds` | P95 latency for DB endpoints |

---

## 🧪 Load Testing

### Prerequisites

Install k6:
```bash
# Windows (Chocolatey)
choco install k6

# macOS
brew install k6

# Linux
sudo apt install k6
```

### Skenario 1: Beban Normal

**Perintah:**
```bash
cd "k6 load test"
k6 run normal_load.js
```

**Konfigurasi:**
| Parameter | Nilai |
|-----------|-------|
| User Konkuren | 10 VU |
| Durasi | 1 menit |
| Target RPS | 15-25 |
| Pattern | Constant |

**Endpoint yang Diuji:**
| Endpoint | Method | Weight |
|----------|--------|--------|
| `/api/summary` | GET | 35% |
| `/api/transactions` | GET | 35% |
| `/api/login` | POST | 15% |
| `/api/register` | POST | 15% |

---

### Skenario 2: Beban Tinggi (Stress Test)

**Perintah:**
```bash
cd "k6 load test"
k6 run high_load.js
```

**Konfigurasi:**
| Parameter | Nilai |
|-----------|-------|
| User Konkuren | 0 → 25 → 50 → 25 → 0 VU |
| Peak Users | 50 VU |
| Durasi | 2 menit |
| Target RPS | 80-150 (peak) |
| Pattern | Ramping |

**Stages:**
1. Ramp Up (20s): 0 → 25 VU
2. Ramp Up (20s): 25 → 50 VU
3. Peak Load (40s): 50 VU sustained
4. Cool Down (20s): 50 → 25 VU
5. Cool Down (20s): 25 → 0 VU

**Endpoint yang Diuji:**
| Endpoint | Method | Weight |
|----------|--------|--------|
| `/api/summary` | GET | 25% |
| `/api/transactions` | GET | 25% |
| `/api/login` (valid) | POST | 10% |
| `/api/login` (invalid) | POST | 15% |
| `/api/register` | POST | 10% |
| `/api/nonexistent` | GET | 15% |

---

## 📁 Project Structure

```
.
├── backend/
│   ├── public/
│   │   └── index.php          # API entry point
│   ├── src/
│   │   ├── Config/            # Database configuration
│   │   └── Controllers/       # API controllers
│   ├── Dockerfile             # PHP-FPM container
│   ├── nginx.conf             # Nginx configuration
│   └── composer.json          # PHP dependencies
│
├── frontend/
│   ├── src/
│   │   ├── views/             # Vue components
│   │   ├── router/            # Vue Router
│   │   └── api.js             # Axios client
│   ├── Dockerfile             # Frontend container
│   └── package.json           # Node dependencies
│
├── database/
│   └── init.sql               # Database schema
│
├── monitoring/
│   ├── prometheus/
│   │   └── prometheus.yml     # Prometheus config
│   └── grafana/
│       ├── provisioning/      # Auto-provisioning
│       └── dashboards/        # Dashboard JSON
│
├── k6 load test/
│   ├── normal_load.js         # Normal load scenario
│   ├── high_load.js           # High load scenario
│   └── README.md              # Load test documentation
│
└── docker-compose.yml         # Container orchestration
```

---

## 📝 API Endpoints

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/register` | POST | Register new user |
| `/api/login` | POST | User login |
| `/api/transactions` | GET | List all transactions |
| `/api/transactions` | POST | Create transaction |
| `/api/transactions/:id` | PUT | Update transaction |
| `/api/transactions/:id` | DELETE | Delete transaction |
| `/api/summary` | GET | Get financial summary |
| `/api/metrics` | GET | Prometheus metrics |

---

## 👨‍💻 Author

Built with ❤️ for Cloud Services Final Project

---

## 📄 License

This project is for educational purposes.
