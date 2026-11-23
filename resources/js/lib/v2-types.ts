export interface AgentItem {
  uid: string;
  name: string;
  hostname: string;
  status: string;
  agentVersion: string | null;
  lastSeenAt: string | null;
  createdAt: string | null;
  profilesCount: number;
  runsCount: number;
}

export interface StorageAccountItem {
  uid: string;
  name: string;
  provider: string;
  accountEmail: string;
  status: string;
  createdAt: string | null;
}

export interface BackupSourceItem {
  uid?: string;
  name: string;
  path: string;
  exclusions: string[];
}

export interface BackupDestinationItem {
  uid?: string;
  storageAccountUid: string | null;
  storageAccountName?: string | null;
  storageAccountEmail?: string | null;
  remoteFolderId: string;
  isRequired: boolean;
}

export interface BackupScheduleItem {
  isEnabled: boolean;
  scheduleType: string;
  cronExpression: string;
  timezone: string;
  nextRunAt: string | null;
}

export interface BackupProfileItem {
  uid: string;
  name: string;
  description: string | null;
  isActive: boolean;
  agentUid: string | null;
  agentName: string | null;
  latestVersion: number;
  encryptArchive: boolean;
  encryptFileNames: boolean;
  compressionLevel: number;
  keepLocalDefault: boolean;
  sources: BackupSourceItem[];
  destinations: BackupDestinationItem[];
  schedule: BackupScheduleItem | null;
}

export interface BackupUploadItem {
  uid: string;
  status: string;
  remoteFileId: string | null;
  storageAccountName: string | null;
  uploadedAt: string | null;
  errorMessage: string | null;
}

export interface BackupArtifactItem {
  uid: string;
  filename: string;
  fileSize: number;
  checksumSha256: string | null;
  localStatus: string;
  uploads: BackupUploadItem[];
}

export interface BackupRunEventItem {
  uid: string;
  eventType: string;
  message: string | null;
  createdAt: string | null;
}

export interface BackupRunItem {
  uid: string;
  profileName: string | null;
  agentName: string | null;
  triggerType: string;
  status: string;
  startedAt: string | null;
  completedAt: string | null;
  errorMessage: string | null;
  artifacts: BackupArtifactItem[];
  events: BackupRunEventItem[];
}

export interface AuditLogItem {
  uid: string;
  action: string;
  subjectType: string;
  userName: string;
  ipAddress: string | null;
  createdAt: string | null;
}
