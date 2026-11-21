<script lang="ts">
  import { onMount } from "svelte";
  import { router, useForm } from "@inertiajs/svelte";
  import { slide } from "svelte/transition";
  import { Button } from "@/components/ui/button/index.js";
  import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card/index.js";
  import { Checkbox } from "@/components/ui/checkbox/index.js";
  import { Input } from "@/components/ui/input/index.js";
  import { Label } from "@/components/ui/label/index.js";
  import { Select } from "@/components/ui/select/index.js";
  import type { BackupScheduleItem, BackupStorageItem } from "@/lib/backup-types.js";
  import { getButtonMenuStyle, getContextMenuStyle } from "@/lib/context-menu.js";
  import { restoreDialogAction, setActionParams } from "@/lib/url-state.js";
  import ActionMenu from "./ActionMenu.svelte";
  import DeleteConfirmDialog from "./DeleteConfirmDialog.svelte";
  import StatusBadge from "./StatusBadge.svelte";

  interface Props {
    schedules: BackupScheduleItem[];
    storages: BackupStorageItem[];
    onstatus?: (message: string) => void;
  }

  let { schedules, storages, onstatus }: Props = $props();

  let isDialogOpen = $state(false);
  let editingId = $state<number | null>(null);
  let deletingId = $state<number | null>(null);
  let menuSchedule = $state<BackupScheduleItem | null>(null);
  let menuStyle = $state("");
  let detailSchedule = $state<BackupScheduleItem | null>(null);
  let copiedUid = $state(false);
  let confirmDeleteSchedule = $state<BackupScheduleItem | null>(null);

  const form = useForm({
    name: "",
    type: "files" as "files" | "database",
    filename_pattern: "backup-{Ymd-His}",
    source_path: "",
    drivers: "mysql",
    database_name: "",
    include: "",
    exclude: "",
    local_destination_path: "/www/backup/sysadmin/others",
    r2_destination_path: "",
    keep_local_backup: false,
    is_sync_cloud: false,
    server_id: "" as string | number,
    storage_id: "" as string | number,
    interval_value: 3,
    interval_unit: "days" as "minutes" | "hours" | "days" | "weeks" | "months",
    max_count_backup: 3,
    is_enabled: true,
  });

  onMount(() => {
    restoreDialogAction(schedules, {
      onCreate: openCreate,
      onDetail: openDetail,
      onEdit: openEdit,
      onDelete: (schedule) => {
        confirmDeleteSchedule = schedule;
      },
    });
  });

  function openDetail(schedule: BackupScheduleItem) {
    copiedUid = false;
    detailSchedule = schedule;
    setActionParams("detail", schedule.uid);
  }

  function closeDetail() {
    copiedUid = false;
    detailSchedule = null;
    setActionParams(null);
  }

  async function copyUid(uid: string) {
    try {
      await navigator.clipboard.writeText(uid);
      copiedUid = true;
      setTimeout(() => {
        copiedUid = false;
      }, 1500);
    } catch {
      copiedUid = false;
    }
  }

  function formatBytes(bytes: number): string {
    if (!bytes || bytes <= 0) return "0 B";
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

  function openCreate() {
    editingId = null;
    form.reset();
    form.type = "files";
    form.filename_pattern = "backup-{Ymd-His}";
    form.drivers = "mysql";
    form.local_destination_path = "/www/backup/sysadmin/others";
    form.interval_value = 3;
    form.interval_unit = "days";
    form.max_count_backup = 3;
    form.is_enabled = true;
    form.clearErrors();
    isDialogOpen = true;
    setActionParams("create");
  }

  function openEdit(schedule: BackupScheduleItem) {
    editingId = schedule.id;
    form.clearErrors();
    form.name = schedule.name;
    form.type = schedule.type;
    form.filename_pattern = schedule.filenamePattern;
    form.source_path = schedule.sourcePath ?? "";
    form.drivers = schedule.drivers ?? "mysql";
    form.database_name = schedule.databaseName ?? "";
    form.include = (schedule.include ?? []).join(", ");
    form.exclude = (schedule.exclude ?? []).join(", ");
    form.local_destination_path = schedule.localDestinationPath ?? "/www/backup/sysadmin/others";
    form.r2_destination_path = schedule.r2DestinationPath ?? "";
    form.keep_local_backup = schedule.keepLocalBackup;
    form.is_sync_cloud = schedule.isSyncCloud;
    form.server_id = schedule.serverId ?? "";
    form.storage_id = schedule.storageId ?? "";
    form.interval_value = schedule.intervalValue;
    form.interval_unit = schedule.intervalUnit;
    form.max_count_backup = schedule.maxCountBackup;
    form.is_enabled = schedule.isEnabled;
    isDialogOpen = true;
    setActionParams("edit", schedule.uid);
  }

  function closeDialog() {
    isDialogOpen = false;
    editingId = null;
    form.reset();
    form.type = "files";
    form.filename_pattern = "backup-{Ymd-His}";
    form.drivers = "mysql";
    form.local_destination_path = "/www/backup/sysadmin/others";
    form.interval_value = 3;
    form.interval_unit = "days";
    form.max_count_backup = 3;
    form.is_enabled = true;
    form.clearErrors();
    setActionParams(null);
  }

  function handleSubmit(event: SubmitEvent) {
    event.preventDefault();

    const options = {
      preserveScroll: true,
      onSuccess: (page: { props: unknown }) => {
        const nextStatus = (page.props as { status?: string | null }).status;
        if (nextStatus) {
          onstatus?.(nextStatus);
        }
        closeDialog();
      },
    };

    if (editingId !== null) {
      form.put(`/client/backup-schedules/${editingId}`, options);
      return;
    }

    form.post("/client/backup-schedules", options);
  }

  function handleDelete(id: number) {
    deletingId = id;
    router.delete(`/client/backup-schedules/${id}`, {
      preserveScroll: true,
      onSuccess: (page) => {
        const nextStatus = (page.props as { status?: string | null }).status;
        if (nextStatus) {
          onstatus?.(nextStatus);
        }
        if (editingId === id) {
          closeDialog();
        }
      },
      onFinish: () => {
        deletingId = null;
        confirmDeleteSchedule = null;
        setActionParams(null);
      },
    });
  }

  function openContextMenu(event: MouseEvent, schedule: BackupScheduleItem) {
    event.preventDefault();
    menuStyle = getContextMenuStyle(event, 160, 124);
    menuSchedule = schedule;
  }

  function openButtonMenu(event: MouseEvent, schedule: BackupScheduleItem) {
    event.stopPropagation();
    if (menuSchedule?.id === schedule.id) {
      closeMenu();
      return;
    }
    menuStyle = getButtonMenuStyle(event.currentTarget as HTMLElement, 160, 124);
    menuSchedule = schedule;
  }

  function closeMenu() {
    menuSchedule = null;
  }

  function handleMenuDetail() {
    if (!menuSchedule) return;
    const schedule = menuSchedule;
    closeMenu();
    openDetail(schedule);
  }

  function handleMenuEdit() {
    if (!menuSchedule) return;
    const schedule = menuSchedule;
    closeMenu();
    openEdit(schedule);
  }

  function handleMenuDelete() {
    if (!menuSchedule) return;
    const schedule = menuSchedule;
    closeMenu();
    confirmDeleteSchedule = schedule;
    setActionParams("delete", schedule.uid);
  }

  function closeConfirmDelete() {
    if (deletingId !== null) return;
    confirmDeleteSchedule = null;
    setActionParams(null);
  }

  function confirmDelete() {
    if (!confirmDeleteSchedule) return;
    handleDelete(confirmDeleteSchedule.id);
  }
</script>

<svelte:window
  onkeydown={(e) => {
    if (e.key === "Escape" && !menuSchedule) {
      if (detailSchedule) {
        closeDetail();
        return;
      }
      if (isDialogOpen && !confirmDeleteSchedule) {
        closeDialog();
      }
    }
  }}
/>

<div class="space-y-4">
  <div class="rounded-xl border border-line bg-page-elevated p-5 sm:p-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
      <div class="flex items-start gap-3">
        <span
          class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md border border-line bg-page text-accent"
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-5">
            <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/>
            <line x1="16" x2="16" y1="2" y2="6"/>
            <line x1="8" x2="8" y1="2" y2="6"/>
            <line x1="3" x2="21" y1="10" y2="10"/>
          </svg>
        </span>
        <div class="min-w-0">
          <h2 class="text-lg font-semibold tracking-tight text-ink">Backup Schedules</h2>
          <p class="mt-1 text-sm leading-6 text-muted">
            Create and manage automated file or database backup schedules.
          </p>
        </div>
      </div>

      <Button type="button" onclick={openCreate} class="shrink-0">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1.5 size-4">
          <line x1="12" y1="5" x2="12" y2="19"/>
          <line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        Add Schedule
      </Button>
    </div>
  </div>

  <div class="rounded-xl border border-line bg-page-elevated p-5 sm:p-6">
    {#if schedules.length === 0}
      <div class="rounded-lg border border-dashed border-line p-8 text-center">
        <p class="text-sm font-medium text-ink">No backup schedules configured yet.</p>
        <p class="mt-1 text-xs text-muted">Create a backup schedule to automate your file or database backups.</p>
        <div class="mt-4">
          <Button type="button" size="sm" onclick={openCreate}>
            Add Schedule
          </Button>
        </div>
      </div>
    {:else}
      <div role="list" class="space-y-3">
        {#each schedules as schedule (schedule.id)}
          <div
            role="listitem"
            oncontextmenu={(e) => openContextMenu(e, schedule)}
            class="flex items-center justify-between gap-4 rounded-lg border p-4 transition-colors duration-100 {menuSchedule?.id === schedule.id
              ? 'border-accent/50 bg-surface-muted/40'
              : 'border-line bg-page hover:border-line-strong'}"
          >
            <div class="min-w-0 space-y-1">
              <div class="flex flex-wrap items-center gap-2">
                <p class="truncate text-sm font-semibold text-ink">{schedule.name}</p>
                <StatusBadge
                  label={schedule.isEnabled ? "Enabled" : "Disabled"}
                  variant={schedule.isEnabled ? "success" : "neutral"}
                />
                <StatusBadge
                  label={schedule.type}
                  variant={schedule.type === "database" ? "info" : "warning"}
                />
              </div>
              <p class="text-xs text-muted">
                Every {schedule.intervalValue} {schedule.intervalUnit} &middot;
                {schedule.type === "database"
                  ? `${schedule.drivers ?? "db"}:${schedule.databaseName ?? "-"}`
                  : schedule.sourcePath ?? "-"}
                &middot; Retention: {schedule.maxCountBackup}
              </p>
            </div>

            <Button
              type="button"
              variant="ghost"
              size="icon"
              aria-label="Schedule actions"
              aria-haspopup="menu"
              aria-expanded={menuSchedule?.id === schedule.id}
              onclick={(e) => openButtonMenu(e, schedule)}
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

{#if isDialogOpen}
  <div
    role="dialog"
    aria-modal="true"
    aria-labelledby="schedule-dialog-title"
    tabindex="-1"
    class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto p-4"
  >
    <button
      type="button"
      class="fixed inset-0 bg-black/50 backdrop-blur-xs"
      onclick={closeDialog}
      aria-label="Close modal"
      tabindex="-1"
    ></button>

    <div class="relative z-10 my-8 w-full max-w-2xl">
      <Card class="shadow-lg max-h-[90vh] flex flex-col">
        <CardHeader class="shrink-0">
          <div class="flex items-center justify-between gap-2">
            <CardTitle id="schedule-dialog-title">
              {editingId !== null ? "Edit Backup Schedule" : "Add Backup Schedule"}
            </CardTitle>
            <button
              type="button"
              onclick={closeDialog}
              class="cursor-pointer rounded-md p-1 text-muted hover:text-ink"
              aria-label="Close"
            >
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"/>
                <line x1="6" y1="6" x2="18" y2="18"/>
              </svg>
            </button>
          </div>
          <CardDescription>
            {editingId !== null
              ? "Update automated schedule parameters and retention settings."
              : "Create a new automated file or database backup schedule."}
          </CardDescription>
        </CardHeader>

        <CardContent class="overflow-y-auto">
          <form onsubmit={handleSubmit} novalidate class="space-y-4">
            <div class="grid gap-4 sm:grid-cols-2">
              <div class="grid gap-2">
                <Label for="schedule-name">Name</Label>
                <Input
                  id="schedule-name"
                  type="text"
                  placeholder="Daily Web Files"
                  bind:value={form.name}
                  disabled={form.processing}
                  aria-invalid={Boolean(form.errors.name)}
                />
                {#if form.errors.name}
                  <p class="text-xs text-destructive">{form.errors.name}</p>
                {/if}
              </div>

              <div class="grid gap-2">
                <Label for="schedule-type">Type</Label>
                <Select
                  id="schedule-type"
                  bind:value={form.type}
                  disabled={form.processing}
                  aria-invalid={Boolean(form.errors.type)}
                  options={[
                    { value: "files", label: "Files" },
                    { value: "database", label: "Database" },
                  ]}
                />
                {#if form.errors.type}
                  <p class="text-xs text-destructive">{form.errors.type}</p>
                {/if}
              </div>

              <div class="grid gap-2">
                <Label for="schedule-pattern">Filename Pattern</Label>
                <Input
                  id="schedule-pattern"
                  type="text"
                  placeholder={"backup-{Ymd-His}"}
                  bind:value={form.filename_pattern}
                  disabled={form.processing}
                  aria-invalid={Boolean(form.errors.filename_pattern)}
                />
                {#if form.errors.filename_pattern}
                  <p class="text-xs text-destructive">{form.errors.filename_pattern}</p>
                {/if}
              </div>

              <div class="grid gap-2">
                <Label for="schedule-local-dest">Local Destination Path</Label>
                <Input
                  id="schedule-local-dest"
                  type="text"
                  placeholder="/www/backup/sysadmin/others"
                  bind:value={form.local_destination_path}
                  disabled={form.processing}
                  aria-invalid={Boolean(form.errors.local_destination_path)}
                />
                {#if form.errors.local_destination_path}
                  <p class="text-xs text-destructive">{form.errors.local_destination_path}</p>
                {/if}
              </div>

              {#if form.type === "files"}
                <div class="col-span-full grid gap-4 sm:grid-cols-2" transition:slide={{ duration: 200 }}>
                  <div class="grid gap-2 sm:col-span-2">
                    <Label for="schedule-source">Source Path</Label>
                    <Input
                      id="schedule-source"
                      type="text"
                      placeholder="/www/wwwroot"
                      bind:value={form.source_path}
                      disabled={form.processing}
                      aria-invalid={Boolean(form.errors.source_path)}
                    />
                    {#if form.errors.source_path}
                      <p class="text-xs text-destructive">{form.errors.source_path}</p>
                    {/if}
                  </div>

                  <div class="grid gap-2">
                    <Label for="schedule-include">Include (comma-separated)</Label>
                    <Input
                      id="schedule-include"
                      type="text"
                      placeholder="app, config"
                      bind:value={form.include}
                      disabled={form.processing}
                    />
                  </div>

                  <div class="grid gap-2">
                    <Label for="schedule-exclude">Exclude (comma-separated)</Label>
                    <Input
                      id="schedule-exclude"
                      type="text"
                      placeholder="node_modules, vendor"
                      bind:value={form.exclude}
                      disabled={form.processing}
                    />
                  </div>
                </div>
              {:else}
                <div class="col-span-full grid gap-4 sm:grid-cols-2" transition:slide={{ duration: 200 }}>
                  <div class="grid gap-2">
                    <Label for="schedule-drivers">Driver</Label>
                    <Input
                      id="schedule-drivers"
                      type="text"
                      placeholder="mysql"
                      bind:value={form.drivers}
                      disabled={form.processing}
                      aria-invalid={Boolean(form.errors.drivers)}
                    />
                    {#if form.errors.drivers}
                      <p class="text-xs text-destructive">{form.errors.drivers}</p>
                    {/if}
                  </div>

                  <div class="grid gap-2">
                    <Label for="schedule-db-name">Database Name</Label>
                    <Input
                      id="schedule-db-name"
                      type="text"
                      placeholder="my_database"
                      bind:value={form.database_name}
                      disabled={form.processing}
                      aria-invalid={Boolean(form.errors.database_name)}
                    />
                    {#if form.errors.database_name}
                      <p class="text-xs text-destructive">{form.errors.database_name}</p>
                    {/if}
                  </div>
                </div>
              {/if}

              <div class="grid gap-2">
                <Label for="schedule-interval-value">Interval Value</Label>
                <Input
                  id="schedule-interval-value"
                  type="number"
                  min={1}
                  bind:value={form.interval_value}
                  disabled={form.processing}
                  aria-invalid={Boolean(form.errors.interval_value)}
                />
                {#if form.errors.interval_value}
                  <p class="text-xs text-destructive">{form.errors.interval_value}</p>
                {/if}
              </div>

              <div class="grid gap-2">
                <Label for="schedule-interval-unit">Interval Unit</Label>
                <Select
                  id="schedule-interval-unit"
                  bind:value={form.interval_unit}
                  disabled={form.processing}
                  options={[
                    { value: "minutes", label: "Minutes" },
                    { value: "hours", label: "Hours" },
                    { value: "days", label: "Days" },
                    { value: "weeks", label: "Weeks" },
                    { value: "months", label: "Months" },
                  ]}
                />
              </div>

              <div class="grid gap-2">
                <Label for="schedule-max-count">Max Retention Count</Label>
                <Input
                  id="schedule-max-count"
                  type="number"
                  min={1}
                  bind:value={form.max_count_backup}
                  disabled={form.processing}
                  aria-invalid={Boolean(form.errors.max_count_backup)}
                />
                {#if form.errors.max_count_backup}
                  <p class="text-xs text-destructive">{form.errors.max_count_backup}</p>
                {/if}
              </div>

              <div class="grid gap-2">
                <Label for="schedule-server">Server Storage</Label>
                <Select
                  id="schedule-server"
                  bind:value={form.server_id}
                  disabled={form.processing}
                  options={[
                    { value: "", label: "None" },
                    ...storages.map((storage) => ({
                      value: storage.id,
                      label: storage.name,
                    })),
                  ]}
                />
              </div>

              {#if form.is_sync_cloud}
                <div class="col-span-full grid gap-4 sm:grid-cols-2" transition:slide={{ duration: 200 }}>
                  <div class="grid gap-2">
                    <Label for="schedule-cloud-storage">Cloud Storage</Label>
                    <Select
                      id="schedule-cloud-storage"
                      bind:value={form.storage_id}
                      disabled={form.processing}
                      aria-invalid={Boolean(form.errors.storage_id)}
                      options={[
                        { value: "", label: "Select storage" },
                        ...storages.map((storage) => ({
                          value: storage.id,
                          label: storage.name,
                        })),
                      ]}
                    />
                    {#if form.errors.storage_id}
                      <p class="text-xs text-destructive">{form.errors.storage_id}</p>
                    {/if}
                  </div>

                  <div class="grid gap-2">
                    <Label for="schedule-r2-dest">Cloud Destination Path</Label>
                    <Input
                      id="schedule-r2-dest"
                      type="text"
                      placeholder="backups/project"
                      bind:value={form.r2_destination_path}
                      disabled={form.processing}
                    />
                  </div>
                </div>
              {/if}
            </div>

            <div class="grid gap-3 sm:grid-cols-3 pt-2">
              <label class="flex h-9 cursor-pointer items-center gap-2.5 rounded-md border border-input bg-page-elevated px-3 text-sm text-ink shadow-xs transition-colors duration-100 hover:border-accent/40">
                <Checkbox bind:checked={form.keep_local_backup} disabled={form.processing} />
                <span>Keep local backup</span>
              </label>

              <label class="flex h-9 cursor-pointer items-center gap-2.5 rounded-md border border-input bg-page-elevated px-3 text-sm text-ink shadow-xs transition-colors duration-100 hover:border-accent/40">
                <Checkbox bind:checked={form.is_sync_cloud} disabled={form.processing} />
                <span>Sync cloud</span>
              </label>

              <label class="flex h-9 cursor-pointer items-center gap-2.5 rounded-md border border-input bg-page-elevated px-3 text-sm text-ink shadow-xs transition-colors duration-100 hover:border-accent/40">
                <Checkbox bind:checked={form.is_enabled} disabled={form.processing} />
                <span>Enabled</span>
              </label>
            </div>

            <div class="flex items-center justify-end gap-2 pt-4 border-t border-line">
              <Button type="button" variant="outline" onclick={closeDialog} disabled={form.processing}>
                Cancel
              </Button>
              <Button type="submit" disabled={form.processing}>
                {form.processing
                  ? "Saving…"
                  : editingId !== null
                    ? "Update schedule"
                    : "Create schedule"}
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>
    </div>
  </div>
{/if}

{#if detailSchedule}
  <div
    role="dialog"
    aria-modal="true"
    aria-labelledby="schedule-detail-dialog-title"
    tabindex="-1"
    class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto p-4"
  >
    <button
      type="button"
      class="fixed inset-0 bg-black/50 backdrop-blur-xs"
      onclick={closeDetail}
      aria-label="Close modal"
      tabindex="-1"
    ></button>

    <div class="relative z-10 my-8 w-full max-w-5xl">
      <Card class="shadow-lg max-h-[90vh] flex flex-col">
        <CardHeader class="shrink-0">
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0 space-y-1.5">
              <div class="flex flex-wrap items-center gap-2">
                <CardTitle id="schedule-detail-dialog-title" class="truncate">
                  {detailSchedule.name}
                </CardTitle>
                <StatusBadge
                  label={detailSchedule.isEnabled ? "Enabled" : "Disabled"}
                  variant={detailSchedule.isEnabled ? "success" : "neutral"}
                />
                <StatusBadge
                  label={detailSchedule.type}
                  variant={detailSchedule.type === "database" ? "info" : "warning"}
                />
              </div>
              <div class="flex items-center gap-1.5">
                <CardDescription class="font-mono text-xs">
                  UID: {detailSchedule.uid}
                </CardDescription>
                <button
                  type="button"
                  onclick={() => detailSchedule && copyUid(detailSchedule.uid)}
                  class="inline-flex cursor-pointer items-center justify-center rounded p-1 text-muted transition-colors hover:bg-surface-muted hover:text-ink"
                  aria-label="Copy UID"
                  title={copiedUid ? "Copied" : "Copy UID"}
                >
                  {#if copiedUid}
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-3.5 text-accent">
                      <polyline points="20 6 9 17 4 12"/>
                    </svg>
                  {:else}
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-3.5">
                      <rect width="14" height="14" x="8" y="8" rx="2" ry="2"/>
                      <path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/>
                    </svg>
                  {/if}
                </button>
              </div>
            </div>
            <button
              type="button"
              onclick={closeDetail}
              class="cursor-pointer rounded-md p-1 text-muted hover:text-ink"
              aria-label="Close"
            >
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"/>
                <line x1="6" y1="6" x2="18" y2="18"/>
              </svg>
            </button>
          </div>
        </CardHeader>

        <CardContent class="overflow-y-auto space-y-5">
          <div class="grid gap-4 rounded-lg border border-line bg-page p-4 sm:grid-cols-2">
            <div>
              <p class="text-[11px] font-medium uppercase tracking-wider text-muted">Interval</p>
              <p class="mt-1 text-sm text-ink">Every {detailSchedule.intervalValue} {detailSchedule.intervalUnit}</p>
            </div>
            <div>
              <p class="text-[11px] font-medium uppercase tracking-wider text-muted">Retention</p>
              <p class="mt-1 text-sm text-ink">{detailSchedule.maxCountBackup} backups (Current: {detailSchedule.countBackup})</p>
            </div>
            <div>
              <p class="text-[11px] font-medium uppercase tracking-wider text-muted">Filename Pattern</p>
              <p class="mt-1 font-mono text-xs text-ink break-all">{detailSchedule.filenamePattern || "—"}</p>
            </div>
            <div>
              <p class="text-[11px] font-medium uppercase tracking-wider text-muted">Total Backup Size</p>
              <p class="mt-1 text-sm text-ink">{formatBytes(detailSchedule.sumFileSize)}</p>
            </div>
            <div>
              <p class="text-[11px] font-medium uppercase tracking-wider text-muted">Next Backup</p>
              <p class="mt-1 text-sm text-ink">{formatDateTime(detailSchedule.nextBackupAt)}</p>
            </div>
            <div>
              <p class="text-[11px] font-medium uppercase tracking-wider text-muted">Last Backup</p>
              <p class="mt-1 text-sm text-ink">{formatDateTime(detailSchedule.lastBackupAt)}</p>
            </div>
          </div>

          <div class="space-y-3 rounded-lg border border-line bg-page p-4">
            <h3 class="text-xs font-semibold uppercase tracking-wider text-muted">Source Configuration</h3>
            {#if detailSchedule.type === "database"}
              <div class="grid gap-4 sm:grid-cols-2">
                <div>
                  <p class="text-[11px] font-medium uppercase tracking-wider text-muted">Database Driver</p>
                  <p class="mt-1 text-sm text-ink">{detailSchedule.drivers ?? "—"}</p>
                </div>
                <div>
                  <p class="text-[11px] font-medium uppercase tracking-wider text-muted">Database Name</p>
                  <p class="mt-1 font-mono text-xs text-ink break-all">{detailSchedule.databaseName ?? "—"}</p>
                </div>
              </div>
            {:else}
              <div class="space-y-3">
                <div>
                  <p class="text-[11px] font-medium uppercase tracking-wider text-muted">Source Path</p>
                  <p class="mt-1 font-mono text-xs text-ink break-all">{detailSchedule.sourcePath ?? "—"}</p>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                  <div>
                    <p class="text-[11px] font-medium uppercase tracking-wider text-muted">Include</p>
                    <p class="mt-1 font-mono text-xs text-ink break-all">
                      {detailSchedule.include.length > 0 ? detailSchedule.include.join(", ") : "—"}
                    </p>
                  </div>
                  <div>
                    <p class="text-[11px] font-medium uppercase tracking-wider text-muted">Exclude</p>
                    <p class="mt-1 font-mono text-xs text-ink break-all">
                      {detailSchedule.exclude.length > 0 ? detailSchedule.exclude.join(", ") : "—"}
                    </p>
                  </div>
                </div>
              </div>
            {/if}
          </div>

          <div class="space-y-3 rounded-lg border border-line bg-page p-4">
            <h3 class="text-xs font-semibold uppercase tracking-wider text-muted">Storage & Destinations</h3>
            <div class="grid gap-4 sm:grid-cols-2">
              <div>
                <p class="text-[11px] font-medium uppercase tracking-wider text-muted">Backup Storage</p>
                <p class="mt-1 text-sm text-ink">{detailSchedule.storageName ?? "—"}</p>
              </div>
              <div>
                <p class="text-[11px] font-medium uppercase tracking-wider text-muted">Target Server</p>
                <p class="mt-1 text-sm text-ink">{detailSchedule.serverName ?? "—"}</p>
              </div>
              <div>
                <p class="text-[11px] font-medium uppercase tracking-wider text-muted">Keep Local Backup</p>
                <p class="mt-1 text-sm text-ink">{detailSchedule.keepLocalBackup ? "Yes" : "No"}</p>
              </div>
              <div>
                <p class="text-[11px] font-medium uppercase tracking-wider text-muted">Local Destination Path</p>
                <p class="mt-1 font-mono text-xs text-ink break-all">{detailSchedule.localDestinationPath ?? "—"}</p>
              </div>
              <div>
                <p class="text-[11px] font-medium uppercase tracking-wider text-muted">Sync to Cloud</p>
                <p class="mt-1 text-sm text-ink">{detailSchedule.isSyncCloud ? "Yes" : "No"}</p>
              </div>
              <div>
                <p class="text-[11px] font-medium uppercase tracking-wider text-muted">Cloud Destination Path</p>
                <p class="mt-1 font-mono text-xs text-ink break-all">{detailSchedule.r2DestinationPath ?? "—"}</p>
              </div>
            </div>
          </div>

          <div class="flex items-center justify-end gap-2 border-t border-line pt-4">
            <Button type="button" variant="outline" onclick={closeDetail}>
              Close
            </Button>
            <Button
              type="button"
              onclick={() => {
                const target = detailSchedule;
                closeDetail();
                if (target) openEdit(target);
              }}
            >
              Edit
            </Button>
          </div>
        </CardContent>
      </Card>
    </div>
  </div>
{/if}

<ActionMenu
  open={Boolean(menuSchedule)}
  style={menuStyle}
  showDetail={true}
  deleting={menuSchedule !== null && deletingId === menuSchedule.id}
  ondetail={handleMenuDetail}
  onedit={handleMenuEdit}
  ondelete={handleMenuDelete}
  onclose={closeMenu}
/>

<DeleteConfirmDialog
  open={Boolean(confirmDeleteSchedule)}
  titleId="schedule-delete-dialog-title"
  title="Delete Backup Schedule"
  itemName={confirmDeleteSchedule?.name ?? ""}
  deleting={deletingId !== null}
  onclose={closeConfirmDelete}
  onconfirm={confirmDelete}
/>
