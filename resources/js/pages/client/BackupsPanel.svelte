<script lang="ts">
  import { onMount } from "svelte";
  import { router } from "@inertiajs/svelte";
  import { Button } from "@/components/ui/button/index.js";
  import type { BackupItem } from "@/lib/backup-types.js";
  import { getButtonMenuStyle, getContextMenuStyle } from "@/lib/context-menu.js";
  import { restoreDialogAction, setActionParams } from "@/lib/url-state.js";
  import ActionMenu from "./ActionMenu.svelte";
  import DeleteConfirmDialog from "./DeleteConfirmDialog.svelte";
  import StatusBadge from "./StatusBadge.svelte";

  interface Props {
    backups: BackupItem[];
    onstatus?: (message: string) => void;
  }

  let { backups, onstatus }: Props = $props();

  let deletingId = $state<number | null>(null);
  let menuBackup = $state<BackupItem | null>(null);
  let menuStyle = $state("");
  let confirmDeleteBackup = $state<BackupItem | null>(null);

  onMount(() => {
    restoreDialogAction(backups, {
      onDelete: (backup) => {
        confirmDeleteBackup = backup;
      },
    });
  });

  function formatBytes(bytes: number | null): string {
    if (bytes === null || bytes <= 0) return "—";
    const units = ["B", "KB", "MB", "GB", "TB"];
    const index = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1);
    const value = bytes / Math.pow(1024, index);
    return `${value.toFixed(index === 0 ? 0 : 2)} ${units[index]}`;
  }

  function formatDateTime(value: string | null): string {
    if (!value) return "—";
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return "—";
    return date.toLocaleString(undefined, {
      year: "numeric",
      month: "short",
      day: "numeric",
      hour: "2-digit",
      minute: "2-digit",
    });
  }

  function handleDelete(id: number) {
    deletingId = id;
    router.delete(`/client/backups/${id}`, {
      preserveScroll: true,
      onSuccess: (page) => {
        const nextStatus = (page.props as { status?: string | null }).status;
        if (nextStatus) {
          onstatus?.(nextStatus);
        }
      },
      onFinish: () => {
        deletingId = null;
        confirmDeleteBackup = null;
        setActionParams(null);
      },
    });
  }

  function openContextMenu(event: MouseEvent, backup: BackupItem) {
    event.preventDefault();
    menuStyle = getContextMenuStyle(event, 160, 48);
    menuBackup = backup;
  }

  function openButtonMenu(event: MouseEvent, backup: BackupItem) {
    event.stopPropagation();
    if (menuBackup?.id === backup.id) {
      closeMenu();
      return;
    }
    menuStyle = getButtonMenuStyle(event.currentTarget as HTMLElement, 160, 48);
    menuBackup = backup;
  }

  function closeMenu() {
    menuBackup = null;
  }

  function handleMenuDelete() {
    if (!menuBackup) return;
    const backup = menuBackup;
    closeMenu();
    confirmDeleteBackup = backup;
    setActionParams("delete", backup.uid);
  }

  function closeConfirmDelete() {
    if (deletingId !== null) return;
    confirmDeleteBackup = null;
    setActionParams(null);
  }

  function confirmDelete() {
    if (!confirmDeleteBackup) return;
    handleDelete(confirmDeleteBackup.id);
  }
</script>

<div class="space-y-4">
  <div class="rounded-xl border border-line bg-page-elevated p-5 sm:p-6">
    <div class="flex items-start gap-3">
      <span
        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md border border-line bg-page text-accent"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-5">
          <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
          <polyline points="7 10 12 15 17 10"/>
          <line x1="12" x2="12" y1="15" y2="3"/>
        </svg>
      </span>
      <div class="min-w-0">
        <h2 class="text-lg font-semibold tracking-tight text-ink">Backups</h2>
        <p class="mt-1 text-sm leading-6 text-muted">
          View recorded backup runs, file sizes, and execution statuses.
        </p>
      </div>
    </div>
  </div>

  <div class="rounded-xl border border-line bg-page-elevated p-5 sm:p-6">
    {#if backups.length === 0}
      <div class="rounded-lg border border-dashed border-line p-8 text-center">
        <p class="text-sm font-medium text-ink">No backup records yet</p>
        <p class="mt-1 text-xs text-muted">
          Configure a backup schedule to start tracking backup executions here.
        </p>
      </div>
    {:else}
      <div role="list" class="space-y-3">
        {#each backups as backup (backup.id)}
          <div
            role="listitem"
            oncontextmenu={(e) => openContextMenu(e, backup)}
            class="flex items-center justify-between gap-4 rounded-lg border p-4 transition-colors duration-100 {menuBackup?.id === backup.id
              ? 'border-accent/50 bg-surface-muted/40'
              : 'border-line bg-page hover:border-line-strong'}"
          >
            <div class="min-w-0 space-y-1">
              <div class="flex flex-wrap items-center gap-2">
                <p class="truncate text-sm font-semibold text-ink">
                  {backup.fileName || backup.uid}
                </p>
                <StatusBadge
                  label={backup.status}
                  variant={backup.status === "success"
                    ? "success"
                    : backup.status === "failed"
                      ? "danger"
                      : "neutral"}
                />
                {#if backup.type}
                  <StatusBadge
                    label={backup.type}
                    variant={backup.type === "database" || backup.type === "full" ? "info" : "warning"}
                  />
                {/if}
              </div>
              <p class="text-xs text-muted">
                Schedule: {backup.scheduleName ?? "—"} &middot; Storage: {backup.storageName ?? "—"} &middot; Size: {formatBytes(backup.fileSize)} &middot; Started: {formatDateTime(backup.startedAt)}
              </p>
              {#if backup.message}
                <p class="text-xs text-faint">{backup.message}</p>
              {/if}
            </div>

            <Button
              type="button"
              variant="ghost"
              size="icon"
              aria-label="Backup actions"
              aria-haspopup="menu"
              aria-expanded={menuBackup?.id === backup.id}
              onclick={(e) => openButtonMenu(e, backup)}
              class="shrink-0 text-muted hover:text-ink"
            >
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-4">
                <circle cx="12" cy="5" r="1"/>
                <circle cx="12" cy="12" r="1"/>
                <circle cx="12" cy="19" r="1"/>
              </svg>
            </Button>
          </div>
        {/each}
      </div>
    {/if}
  </div>
</div>

<ActionMenu
  open={Boolean(menuBackup)}
  style={menuStyle}
  showEdit={false}
  deleting={menuBackup !== null && deletingId === menuBackup.id}
  ondelete={handleMenuDelete}
  onclose={closeMenu}
/>

<DeleteConfirmDialog
  open={Boolean(confirmDeleteBackup)}
  titleId="backup-delete-dialog-title"
  title="Delete Backup"
  itemName={confirmDeleteBackup?.fileName || confirmDeleteBackup?.uid || ""}
  deleting={deletingId !== null}
  onclose={closeConfirmDelete}
  onconfirm={confirmDelete}
/>
