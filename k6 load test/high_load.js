import http from 'k6/http';
import { check, sleep } from 'k6';
import { Rate, Trend } from 'k6/metrics';

// Custom metrics
const errorRate = new Rate('errors');
const responseTime = new Trend('response_time');

// ======================================
// SKENARIO 2: BEBAN TINGGI (STRESS TEST)
// ======================================
// Simulasi lonjakan traffic/flash sale
// - Peak: 50 Virtual Users (VU) konkuren
// - Durasi: 2 menit dengan ramping pattern
// - Think time: minimal (aggressive)
// - Target: ~80-150 RPS pada peak
// ======================================

export const options = {
    scenarios: {
        high_load: {
            executor: 'ramping-vus',
            startVUs: 0,
            stages: [
                { duration: '20s', target: 25 },   // Ramp up phase 1
                { duration: '20s', target: 50 },   // Ramp up to peak
                { duration: '40s', target: 50 },   // Sustained peak load
                { duration: '20s', target: 25 },   // Cool down phase 1
                { duration: '20s', target: 0 },    // Cool down to zero
            ],
        }
    },
    thresholds: {
        http_req_duration: ['p(95)<3000'],
        http_req_failed: ['rate<0.5'],
    }
};

const BASE_URL = 'http://localhost:8000/api';

// Mixed endpoints including error-generating ones
const ENDPOINTS = [
    { name: 'Summary', method: 'GET', path: '/summary', weight: 25 },
    { name: 'Transactions', method: 'GET', path: '/transactions', weight: 25 },
    { name: 'Login OK', method: 'POST', path: '/login', weight: 10, body: { username: 'demo', password: 'demo123' } },
    { name: 'Login Fail', method: 'POST', path: '/login', weight: 15, body: { username: 'wrong', password: 'wrong' } },
    { name: 'Register', method: 'POST', path: '/register', weight: 10, body: {} },
    { name: 'Not Found', method: 'GET', path: '/nonexistent', weight: 15 },
];

function selectEndpoint() {
    const totalWeight = ENDPOINTS.reduce((sum, ep) => sum + ep.weight, 0);
    let random = Math.random() * totalWeight;
    for (const endpoint of ENDPOINTS) {
        random -= endpoint.weight;
        if (random <= 0) return endpoint;
    }
    return ENDPOINTS[0];
}

export function setup() {
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
    console.log('🔥 SKENARIO 2: BEBAN TINGGI (STRESS TEST)');
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
    console.log('📊 Virtual Users  : 0 → 25 → 50 → 25 → 0');
    console.log('⏱️  Durasi        : 2 menit');
    console.log('🎯 Target RPS     : 80-150 (peak)');
    console.log('⚠️  Expect        : Latency spike, some errors');
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
    return {};
}

export default function () {
    const endpoint = selectEndpoint();

    const params = {
        headers: { 'Content-Type': 'application/json' },
        timeout: '15s'
    };

    let response;
    const startTime = new Date();

    try {
        if (endpoint.method === 'GET') {
            response = http.get(`${BASE_URL}${endpoint.path}`, params);
        } else {
            let body = endpoint.body || {};
            if (endpoint.path === '/register' && !body.username) {
                body = { username: `high_${__VU}_${__ITER}_${Date.now()}`, password: 'test123' };
            }
            response = http.post(`${BASE_URL}${endpoint.path}`, JSON.stringify(body), params);
        }

        const duration = new Date() - startTime;
        responseTime.add(duration);

        check(response, { 'not server error': (r) => r.status < 500 });
        errorRate.add(response.status >= 400);

    } catch (e) {
        errorRate.add(true);
    }

    // Minimal think time - aggressive load
    sleep(Math.random() * 0.2 + 0.1); // 0.1-0.3 detik
}

export function teardown() {
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
    console.log('✅ BEBAN TINGGI - SELESAI');
    console.log('📈 Cek Grafana untuk hasil stress test!');
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
}
