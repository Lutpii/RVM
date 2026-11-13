// Points label for one item in a session summary.
// Rejected items ('unknown', 'reject' or a material the machine does not take)
// earn 0 and lose nothing, so they show a grey "0", never "-0".
export function transactionPoints(transaction) {
  if (transaction?.is_valid) {
    const earned = Number(transaction.points_earned) || 0
    return earned > 0 ? { label: `+${earned}`, tone: 'green' } : { label: '0', tone: 'zero' }
  }
  const deducted = Number(transaction?.points_deducted) || 0
  return deducted > 0 ? { label: `-${deducted}`, tone: 'red' } : { label: '0', tone: 'zero' }
}
