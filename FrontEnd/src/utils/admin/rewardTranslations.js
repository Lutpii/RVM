// Optional English / Indonesian versions of a reward card the admin writes
// in Malay. Each is always sent (trimmed, '' when blank) so clearing a
// translation on edit actually clears it on the server.
export const REWARD_TRANSLATION_FIELDS = [
  'name_en', 'name_id', 'description_en', 'description_id', 'category_en', 'category_id',
]

export function rewardTranslationFields(source) {
  return Object.fromEntries(
    REWARD_TRANSLATION_FIELDS.map((key) => [key, String(source?.[key] ?? '').trim()]),
  )
}
