<script lang="ts">
  import type { LocalePreference } from "@/lib/theme.js";
  import type { AuditLogItem } from "@/lib/v2-types.js";

  interface Props {
    auditLogs: AuditLogItem[];
    locale?: LocalePreference;
  }

  let { auditLogs = [], locale = "en" }: Props = $props();
  const isId = $derived(locale === "id");

  function formatTime(value: string | null): string {
    if (!value) return "—";
    const d = new Date(value);
    return Number.isNaN(d.getTime()) ? "—" : d.toLocaleString();
  }
</script>

<div class="space-y-4">
  <div class="rounded-xl border border-line bg-page-elevated p-5 sm:p-6">
    <div class="flex items-start gap-3">
      <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md border border-line bg-page text-accent">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-5">
          <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
          <polyline points="14 2 14 8 20 8"/>
          <line x1="16" x2="8" y1="13" y2="13"/>
          <line x1="16" x2="8" y1="17" y2="17"/>
        </svg>
      </span>
      <div class="min-w-0">
        <h2 class="text-lg font-semibold tracking-tight text-ink">
          {isId ? "Log Audit" : "Audit Logs"}
        </h2>
        <p class="mt-1 text-sm leading-6 text-muted">
          {isId
            ? "Catatan aktivitas sistem terbaru untuk perubahan konfigurasi dan eksekusi backup."
            : "Recent activity trail for configuration changes and backup dispatches."}
        </p>
      </div>
    </div>
  </div>

  <div class="rounded-xl border border-line bg-page-elevated p-5 sm:p-6">
    {#if auditLogs.length === 0}
      <p class="text-sm text-muted">
        {isId ? "Belum ada catatan audit." : "No audit entries recorded yet."}
      </p>
    {:else}
      <div class="divide-y divide-line">
        {#each auditLogs as log (log.uid)}
          <div class="flex flex-wrap items-center justify-between gap-2 py-3 first:pt-0 last:pb-0">
            <div class="min-w-0">
              <p class="font-mono text-xs font-medium text-ink">{log.action}</p>
              <p class="mt-0.5 text-xs text-muted">
                {log.userName} · {log.subjectType} · {log.ipAddress ?? "—"}
              </p>
            </div>
            <span class="text-xs text-faint">{formatTime(log.createdAt)}</span>
          </div>
        {/each}
      </div>
    {/if}
  </div>
</div>
