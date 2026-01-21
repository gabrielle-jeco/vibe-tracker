import http from 'k6/http';
import { check, sleep } from 'k6';
import { Rate, Trend } from 'k6/metrics';

// Custom metrics
const errorRate = new Rate('errors');
const responseTime = new Trend('response_time');

// ======================================
// SKENARIO 1: BEBAN NORMAL
// ======================================
// Simulasi penggunaan normal sehari-hari
// - 10 Virtual Users (VU) konkuren
// - Durasi: 1 menit
// - Think time: 0.5-1 detik (realistic user behavior)
// - Target: ~15-25 RPS
// ======================================

export const options = {
    scenarios: {
        normal_load: {
            executor: 'constant-vus',
            vus: 10,
            duration: '1m',
        }
    },
    thresholds: {
        http_req_duration: ['p(95)<1000'],
        errors: ['rate<0.3'],
    }
};

const BASE_URL = 'http://localhost:8000/api';

// Endpoint normal usage
const ENDPOINTS = [
    { name: 'Summary', method: 'GET', path: '/summary', weight: 35 },
    { name: 'Transactions', method: 'GET', path: '/transactions', weight: 35 },
    { name: 'Login', method: 'POST', path: '/login', weight: 15, body: { username: 'demo', password: 'demo123' } },
    { name: 'Register', method: 'POST', path: '/register', weight: 15, body: {} },
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
    console.log('🚀 SKENARIO 1: BEBAN NORMAL');
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
    console.log('📊 Virtual Users  : 10');
    console.log('⏱️  Durasi        : 1 menit');
    console.log('🎯 Target RPS     : 15-25');
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
    return {};
}

export default function () {
    const endpoint = selectEndpoint();

    const params = {
        headers: { 'Content-Type': 'application/json' },
        timeout: '10s'
    };

    let response;
    const startTime = new Date();

    try {
        if (endpoint.method === 'GET') {
            response = http.get(`${BASE_URL}${endpoint.path}`, params);
        } else {
            let body = endpoint.body || {};
            if (endpoint.path === '/register' && !body.username) {
                body = { username: `normal_${__VU}_${__ITER}_${Date.now()}`, password: 'test123' };
            }
            response = http.post(`${BASE_URL}${endpoint.path}`, JSON.stringify(body), params);
        }

        const duration = new Date() - startTime;
        responseTime.add(duration);

        check(response, { 'status OK': (r) => r.status < 500 });
        errorRate.add(response.status >= 400);

    } catch (e) {
        errorRate.add(true);
    }

    // Normal user think time
    sleep(Math.random() * 0.5 + 0.5); // 0.5-1 detik
}

export function teardown() {
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
    console.log('✅ BEBAN NORMAL - SELESAI');
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
}
