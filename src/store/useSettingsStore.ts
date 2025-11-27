/**
 * Settings Store
 * Manages app settings including theme preferences
 */

import { create } from 'zustand';
import { persist, createJSONStorage } from 'zustand/middleware';
import * as SecureStore from 'expo-secure-store';
import type { ThemeMode } from '../types/common';
import type { NotificationSettings } from '../types/notifications';

interface SettingsStore {
  // Theme
  themeMode: ThemeMode;
  setThemeMode: (mode: ThemeMode) => void;
  
  // Notifications
  notificationSettings: NotificationSettings;
  updateNotificationSettings: (settings: Partial<NotificationSettings>) => void;
  
  // General
  hapticFeedback: boolean;
  setHapticFeedback: (enabled: boolean) => void;
  
  // Reset
  resetSettings: () => void;
}

const defaultNotificationSettings: NotificationSettings = {
  gradePosted: true,
  attendanceAlert: true,
  scheduleChange: true,
  announcements: true,
  deadlineReminder: true,
  pushEnabled: true,
  emailEnabled: false,
};

// Use SecureStore for settings persistence
const storage = {
  getItem: async (name: string): Promise<string | null> => {
    try {
      return await SecureStore.getItemAsync(name);
    } catch {
      return null;
    }
  },
  setItem: async (name: string, value: string): Promise<void> => {
    try {
      await SecureStore.setItemAsync(name, value);
    } catch {
      // Silently fail
    }
  },
  removeItem: async (name: string): Promise<void> => {
    try {
      await SecureStore.deleteItemAsync(name);
    } catch {
      // Silently fail
    }
  },
};

export const useSettingsStore = create<SettingsStore>()(
  persist(
    (set) => ({
      // Theme
      themeMode: 'system',
      setThemeMode: (mode: ThemeMode) => set({ themeMode: mode }),

      // Notifications
      notificationSettings: defaultNotificationSettings,
      updateNotificationSettings: (settings: Partial<NotificationSettings>) =>
        set((state) => ({
          notificationSettings: {
            ...state.notificationSettings,
            ...settings,
          },
        })),

      // General
      hapticFeedback: true,
      setHapticFeedback: (enabled: boolean) => set({ hapticFeedback: enabled }),

      // Reset
      resetSettings: () =>
        set({
          themeMode: 'system',
          notificationSettings: defaultNotificationSettings,
          hapticFeedback: true,
        }),
    }),
    {
      name: 'settings-storage',
      storage: createJSONStorage(() => storage),
    }
  )
);

export default useSettingsStore;
