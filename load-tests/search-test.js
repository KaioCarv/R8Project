import { check, sleep } from 'k6';
import http from 'k6/http';
import { Counter, Trend } from 'k6/metrics';

const durByStatus = new Trend('dur_by_status', true);
const failures = new Counter('failures');

export const options = {
  discardResponseBodies: true,
  thresholds: {
    'checks{type:status200}': ['rate>0.95'], // queremos >=95% 200
    'http_req_duration{expected_response:true}': ['p(95)<800'],
    'http_req_failed': ['rate<0.01'],
  },
};

const QUERIES = (__ENV.QUERIES || 'game,dexter,whiplash,matrix,avatar,house').split(',');

export default function () {
  const q = QUERIES[Math.floor(Math.random() * QUERIES.length)];
  const res = http.get(${__ENV.K6_URL}?q=${encodeURIComponent(q)}, {
    timeout: '10s',
    redirects: 0,
    tags: { endpoint: 'search' },
  });

  durByStatus.add(res.timings.duration, { status: String(res.status) });

  const ok = check(
    res,
    { 'status é 200': (r) => r.status === 200 },
    { type: 'status200' }                    // <- tag pros thresholds acima
  );

  if (!ok) {
    failures.add(1);
    // log leve: status + primeiros bytes (ajuda a descobrir 429, 500, etc.)
    console.error(FAIL q=${q} status=${res.status} dur=${res.timings.duration}ms);
  }

  sleep(0.1);
}