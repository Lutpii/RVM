// Classifies a rejected /qr/scan or /sessions/start response body.
export function scanOutcome(data) {
  if (data?.code === 'active_session_elsewhere') {
    return { kind: 'elsewhere', machineName: data.machine_name, sessionCode: data.session_code }
  }
  return { kind: 'error', message: data?.message || '' }
}
