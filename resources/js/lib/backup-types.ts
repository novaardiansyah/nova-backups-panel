export interface BackupItem {
  id: number;
  uid: string;
  fileName: string | null;
  fileSize: number | null;
  type: "full" | "database" | "files" | "incremental" | null;
  status: "pending" | "success" | "failed";
  duration: number;
  message: string | null;
  scheduleName: string | null;
  storageName: string | null;
  serverName: string | null;
  startedAt: string | null;
  completedAt: string | null;
}

export interface BackupScheduleItem {
  id: number;
  uid: string;
  name: string;
  type: "files" | "database";
  drivers: string | null;
  databaseName: string | null;
  sourcePath: string | null;
  include: string[];
  exclude: string[];
  localDestinationPath: string | null;
  r2DestinationPath: string | null;
  keepLocalBackup: boolean;
  isSyncCloud: boolean;
  filenamePattern: string;
  countBackup: number;
  maxCountBackup: number;
  sumFileSize: number;
  intervalValue: number;
  intervalUnit: "minutes" | "hours" | "days" | "weeks" | "months";
  nextBackupAt: string | null;
  lastBackupAt: string | null;
  isEnabled: boolean;
  storageId: number | null;
  serverId: number | null;
  storageName: string | null;
  serverName: string | null;
}

export interface BackupStorageItem {
  id: number;
  uid: string;
  name: string;
  slug: string;
  providerId: number | null;
  providerName: string | null;
  keys: Record<string, string>;
  active: boolean;
}

export interface BackupStorageProviderItem {
  id: number;
  uid: string;
  name: string;
  slug: string;
  keys: Record<string, string>;
  storagesCount: number;
}
