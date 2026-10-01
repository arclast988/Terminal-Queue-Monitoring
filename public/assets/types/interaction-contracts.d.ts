/** Existing public contracts: types add no runtime fields or API requirements. */
export type MotionMode = 'full' | 'lite' | 'reduced';
export interface TerminalMotionAPI { getMode(): MotionMode; }
export interface AuthBackgroundConfig {
  slides: readonly string[];
  defaults: readonly string[];
}
export interface AuthBackgroundUpdate {
  category?: string;
  app_bg_mode?: string;
  app_background_image?: string;
  app_bg_slideshow?: string[];
}
export interface TerminalAuthBackgroundAPI {
  mount(config: AuthBackgroundConfig): () => void;
}
export interface GlobalLoaderAPI {
  start(immediate?: boolean): void;
  done(): void;
  set(percent: number): void;
  isRunning(): boolean;
  isVisible(): boolean;
  setDelay(milliseconds: number): void;
  getDelay(): number;
  addSilentPattern(pattern: RegExp): void;
  showButtonSpinner(button: HTMLElement | null, label?: string, immediate?: boolean): void;
  hideButtonSpinner(button: HTMLElement | null): void;
  showTableLoader(container: HTMLElement | string | null, label?: string, immediate?: boolean): void;
  hideTableLoader(container: HTMLElement | string | null): void;
}
export interface VehicleTypeMetadata {
  name: string;
  slug: string;
  color: string | null;
  icon: string | null;
  photo: string | null;
}
/** GET /api/queue-status: matches the current PHP explicit projection. */
export interface QueueStatusItem {
  id: number;
  position: number;
  status: 'waiting' | 'boarding';
  current_passengers: number;
  capacity: number;
  plate_number: string;
  vehicle_type: string | null;
  photo_url: string | null;
  has_custom_photo: boolean;
  driver_name: string | null;
  estimated_departure: string | null;
  origin: string;
  destination: string;
}
export interface QueueStatusResponse {
  success: boolean;
  queue: QueueStatusItem[];
  // PHP encodes an empty associative map as [] when no types exist.
  vehicle_type_colors: Record<string, VehicleTypeMetadata> | [];
  sync_token: string;
  queue_hash: string;
  ts: number;
}
export interface RealtimeMessage {
  type: string;
  action?: string;
  data?: Record<string, unknown>;
}
export interface QueueSyncConfig {
  apiUrl?: string;
  refreshUrl?: string;
  pollInterval?: number;
  tableSelector?: string;
  modalSelector?: string;
  extraRefresh?: (document: Document) => void;
  onlyWS?: boolean;
  customWSHandler?: (message: RealtimeMessage) => void;
  customRefresh?: () => void;
}
export interface QueueSyncAPI {
  init(config: QueueSyncConfig): void;
  refresh(forceAjax?: boolean): void;
  destroy(): void;
  updatePassengerUI(id: number | string, count: number | string, capacity: number | string): void;
  applyVehicleTypeColors(colors: Record<string, string | VehicleTypeMetadata>): void;
}
declare global {
  interface Navigator { readonly deviceMemory?: number; }
  interface Window {
    TerminalMotion?: Readonly<TerminalMotionAPI>;
    TerminalAuthBackground?: Readonly<TerminalAuthBackgroundAPI>;
    GlobalLoader?: GlobalLoaderAPI;
    QueueSync?: QueueSyncAPI;
  }
}
