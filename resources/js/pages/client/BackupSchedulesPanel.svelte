<script lang="ts">
  import { router, useForm } from "@inertiajs/svelte";
  import { Button } from "@/components/ui/button/index.js";
  import { Checkbox } from "@/components/ui/checkbox/index.js";
  import { Input } from "@/components/ui/input/index.js";
  import { Label } from "@/components/ui/label/index.js";
  import { Select } from "@/components/ui/select/index.js";
  import type { BackupScheduleItem, BackupStorageItem } from "@/lib/backup-types.js";

  interface Props {
    schedules: BackupScheduleItem[];
    storages: BackupStorageItem[];
    onstatus?: (message: string) => void;
  }

  let { schedules, storages, onstatus }: Props = $props();

  let editingId = $state<number | null>(null);
  let deletingId = $state<number | null>(null);

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

  function startEdit(schedule: BackupScheduleItem) {
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
  }

  function resetForm() {
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
        resetForm();
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
          resetForm();
        }
      },
      onFinish: () => {
        deletingId = null;
      },
    });
  }
</script>

<div class="space-y-4">
  <div class="rounded-xl border border-line bg-page-elevated p-5 sm:p-6">
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
  </div>

  <form
    class="rounded-xl border border-line bg-page-elevated p-5 sm:p-6"
    onsubmit={handleSubmit}
    novalidate
  >
    <div class="flex items-center justify-between gap-2">
      <h3 class="text-sm font-semibold text-ink">
        {editingId !== null ? "Edit Backup Schedule" : "Add Backup Schedule"}
      </h3>
      {#if editingId !== null}
        <button
          type="button"
          onclick={resetForm}
          class="cursor-pointer text-xs font-medium text-accent hover:underline"
        >
          Cancel edit
        </button>
      {/if}
    </div>

    <div class="mt-4 grid gap-4 sm:grid-cols-2">
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
        >
          <option value="files">Files</option>
          <option value="database">Database</option>
        </Select>
        {#if form.errors.type}
          <p class="text-xs text-destructive">{form.errors.type}</p>
        {/if}
      </div>

      <div class="grid gap-2">
        <Label for="schedule-pattern">Filename Pattern</Label>
        <Input
          id="schedule-pattern"
          type="text"
          placeholder="backup-{Ymd-His}"
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
      {:else}
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
        >
          <option value="minutes">Minutes</option>
          <option value="hours">Hours</option>
          <option value="days">Days</option>
          <option value="weeks">Weeks</option>
          <option value="months">Months</option>
        </Select>
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
        >
          <option value="">None</option>
          {#each storages as storage (storage.id)}
            <option value={storage.id}>{storage.name}</option>
          {/each}
        </Select>
      </div>

      {#if form.is_sync_cloud}
        <div class="grid gap-2">
          <Label for="schedule-cloud-storage">Cloud Storage</Label>
          <Select
            id="schedule-cloud-storage"
            bind:value={form.storage_id}
            disabled={form.processing}
            aria-invalid={Boolean(form.errors.storage_id)}
          >
            <option value="">Select storage</option>
            {#each storages as storage (storage.id)}
              <option value={storage.id}>{storage.name}</option>
            {/each}
          </Select>
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
      {/if}
    </div>

    <div class="mt-4 grid gap-3 sm:grid-cols-3">
      <label class="flex h-9 cursor-pointer items-center gap-2.5 rounded-md border border-input bg-page-elevated px-3 text-sm text-ink shadow-xs">
        <Checkbox bind:checked={form.keep_local_backup} disabled={form.processing} />
        <span>Keep local backup</span>
      </label>

      <label class="flex h-9 cursor-pointer items-center gap-2.5 rounded-md border border-input bg-page-elevated px-3 text-sm text-ink shadow-xs">
        <Checkbox bind:checked={form.is_sync_cloud} disabled={form.processing} />
        <span>Sync cloud</span>
      </label>

      <label class="flex h-9 cursor-pointer items-center gap-2.5 rounded-md border border-input bg-page-elevated px-3 text-sm text-ink shadow-xs">
        <Checkbox bind:checked={form.is_enabled} disabled={form.processing} />
        <span>Enabled</span>
      </label>
    </div>

    <div class="mt-5 flex">
      <Button type="submit" disabled={form.processing}>
        {form.processing
          ? "Saving…"
          : editingId !== null
            ? "Update schedule"
            : "Create schedule"}
      </Button>
    </div>
  </form>

  <div class="rounded-xl border border-line bg-page-elevated p-5 sm:p-6">
    {#if schedules.length === 0}
      <p class="text-sm text-muted">No backup schedules configured yet.</p>
    {:else}
      <div class="space-y-3">
        {#each schedules as schedule (schedule.id)}
          <div class="flex flex-wrap items-center justify-between gap-4 rounded-lg border border-line bg-page p-4">
            <div class="min-w-0 space-y-1">
              <div class="flex flex-wrap items-center gap-2">
                <p class="text-sm font-semibold text-ink">{schedule.name}</p>
                <span
                  class="rounded-full px-2 py-0.5 text-[11px] font-medium {schedule.isEnabled
                    ? 'bg-accent-soft text-accent'
                    : 'bg-surface-muted text-muted'}"
                >
                  {schedule.isEnabled ? "Enabled" : "Disabled"}
                </span>
                <span class="rounded-full border border-line px-2 py-0.5 text-[11px] text-muted">
                  {schedule.type}
                </span>
              </div>
              <p class="text-xs text-muted">
                Every {schedule.intervalValue} {schedule.intervalUnit} &middot;
                {schedule.type === "database"
                  ? `${schedule.drivers ?? "db"}:${schedule.databaseName ?? "-"}`
                  : schedule.sourcePath ?? "-"}
                &middot; Retention: {schedule.maxCountBackup}
              </p>
            </div>

            <div class="flex items-center gap-2">
              <Button type="button" variant="outline" size="sm" onclick={() => startEdit(schedule)}>
                Edit
              </Button>
              <Button
                type="button"
                variant="outline"
                size="sm"
                disabled={deletingId === schedule.id}
                onclick={() => handleDelete(schedule.id)}
              >
                {deletingId === schedule.id ? "Deleting…" : "Delete"}
              </Button>
            </div>
          </div>
        {/each}
      </div>
    {/if}
  </div>
</div>
