/**
 * Theme Context
 * Provides theme-aware colors and theme switching functionality
 */

import React, { createContext, useContext, ReactNode, useMemo } from 'react';
import { useColorScheme } from 'react-native';
import { Colors } from '../constants/Colors';
import { useSettingsStore } from '../store/useSettingsStore';
import type { ThemeMode } from '../types/common';

// Define a more flexible color type
type ColorValue = string;
interface ThemeColors {
  [key: string]: ColorValue;
}

interface ThemeContextType {
  theme: 'light' | 'dark';
  themeMode: ThemeMode;
  colors: ThemeColors;
  isDark: boolean;
  setThemeMode: (mode: ThemeMode) => void;
  toggleTheme: () => void;
}

const ThemeContext = createContext<ThemeContextType | null>(null);

interface ThemeProviderProps {
  children: ReactNode;
}

export function ThemeProvider({ children }: ThemeProviderProps) {
  const systemColorScheme = useColorScheme();
  const { themeMode, setThemeMode } = useSettingsStore();

  // Determine actual theme based on mode
  const theme = useMemo(() => {
    if (themeMode === 'system') {
      return systemColorScheme ?? 'light';
    }
    return themeMode;
  }, [themeMode, systemColorScheme]);

  const isDark = theme === 'dark';
  const colors = Colors[theme];

  const toggleTheme = () => {
    setThemeMode(theme === 'light' ? 'dark' : 'light');
  };

  const value = useMemo(
    () => ({
      theme,
      themeMode,
      colors,
      isDark,
      setThemeMode,
      toggleTheme,
    }),
    [theme, themeMode, colors, isDark]
  );

  return (
    <ThemeContext.Provider value={value}>
      {children}
    </ThemeContext.Provider>
  );
}

export function useTheme(): ThemeContextType {
  const context = useContext(ThemeContext);
  if (!context) {
    throw new Error('useTheme must be used within a ThemeProvider');
  }
  return context;
}

export default ThemeContext;
