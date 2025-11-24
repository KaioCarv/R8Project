import { check, sleep } from 'k6';
import http from 'k6/http';
import { Counter, Trend } from 'k6/metrics';

const durByStatus = new Trend('dur_by_status', true);
const failures = new Counter('failures');

export const options = {
  discardResponseBodies: true,
  thresholds: {
    'checks{type:status200}': ['rate>0.95'],
    'http_req_duration{expected_response:true}': ['p(95)<800'],
    'http_req_failed': ['rate<0.01'],
  },
};

function randInt(min, max) {
  return Math.floor(Math.random() * (max - min + 1)) + min;
}

export default function () {
  const minId = parseInt(__ENV.AVALIACAO_MIN_ID || '1', 10);
  const maxId = parseInt(__ENV.AVALIACAO_MAX_ID || '100', 10);
  const avaliacaoId = randInt(minId, maxId);

  const body = {
    action: 'criar_comentario',
    avaliacao_id: avaliacaoId,
    texto: Comentario de carga VU=${__VU} iter=${__ITER},
  };

  const params = {
    timeout: '10s',
    redirects: 0,
    headers: {
      // se precisar mandar sessão, defina K6_COOKIE: "PHPSESSID=abc123..."
      //'Cookie': __ENV.K6_COOKIE || '',
      'Content-Type': 'application/x-www-form-urlencoded',
    },
    tags: { endpoint: 'criar_comentario' },
  };

  // se quiser usar cookie via env, descomenta abaixo:
  if (__ENV.K6_COOKIE) {
    params.headers.Cookie = __ENV.K6_COOKIE;
  }

  const res = http.post(__ENV.K6_URL, body, params);

  durByStatus.add(res.timings.duration, { status: String(res.status) });

  const ok = check(
    res,
    { 'status é 200': (r) => r.status === 200 },
    { type: 'status200' }
  );

  if (!ok) {
    failures.add(1);
    console.error(
      FAIL avaliacao_id=${avaliacaoId} status=${res.status} dur=${res.timings.duration}ms
    );
  }

  sleep(0.1);
}