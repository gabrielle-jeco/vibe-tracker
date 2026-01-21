# Load Testing dengan k6

## Prerequisites

Install k6:
```powershell
# Windows (dengan Chocolatey)
choco install k6

# Atau download dari https://k6.io/docs/getting-started/installation/
```

---

## Skenario 1: Beban Normal

**File:** `normal_load.js`

| Parameter | Nilai |
|-----------|-------|
| Virtual Users (VU) | 10 konkuren |
| Durasi | 1 menit |
| Think Time | 1-3 detik |
| Target RPS | ~10-20 req/s |

**Endpoint yang diuji:**
- `GET /api/summary` - Ringkasan saldo
- `GET /api/transactions` - Daftar transaksi

**Perintah:**
```powershell
cd "c:\xampp\htdocs\final project cloud services\k6 load test"
k6 run normal_load.js
```

**Threshold (Batas Sukses):**
- 95% request < 500ms
- Error rate < 10%

---

## Skenario 2: Beban Tinggi (Stress Test)

**File:** `high_load.js`

| Parameter | Nilai |
|-----------|-------|
| Virtual Users (VU) | 0 → 50 → 100 → 0 (ramping) |
| Durasi Total | ~3.5 menit |
| Peak Duration | 2 menit @ 100 VU |
| Think Time | 0-0.5 detik |
| Target RPS | ~100-200 req/s pada peak |

**Stages:**
1. **Ramp Up 1** (30s): 0 → 50 VU
2. **Ramp Up 2** (30s): 50 → 100 VU
3. **Peak** (2m): 100 VU sustained
4. **Ramp Down** (30s): 100 → 0 VU

**Endpoint yang diuji:**
- `GET /api/summary` (30% traffic)
- `GET /api/transactions` (40% traffic)
- `POST /api/login` (20% traffic)
- `POST /api/register` (10% traffic)

**Perintah:**
```powershell
cd "c:\xampp\htdocs\final project cloud services\k6 load test"
k6 run high_load.js
```

**Threshold (Batas - lebih longgar untuk stress):**
- 95% request < 2000ms
- Error rate < 50%

---

## Observasi di Grafana

Saat test berjalan, buka Grafana (http://localhost:3000) dan perhatikan:

1. **RPS Panel** → Lonjakan request
2. **P95 Response Time** → Latency meningkat saat beban tinggi
3. **Error Rate** → Mungkin naik saat stress test
4. **CPU Usage** → Spike saat peak load
5. **MySQL Connections** → Bertambah seiring VU

---

## Tips

- Jalankan **normal_load** dulu untuk baseline
- Baru jalankan **high_load** untuk stress test
- Screenshot Grafana sebelum dan sesudah untuk perbandingan
