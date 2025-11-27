/**
 * WMSU Grading System Color Palette
 * Based on Western Mindanao State University's crimson-red identity
 * Supports both Light and Dark modes with Apple-inspired styling
 */

export const Colors = {
  light: {
    // Primary - WMSU Crimson Red
    primary: '#8B0000',
    primaryLight: '#B22222',
    primaryDark: '#5C0000',
    primaryMuted: '#8B000020',

    // Secondary - Gold/Accent (WMSU secondary color)
    secondary: '#DAA520',
    secondaryLight: '#FFD700',
    secondaryDark: '#B8860B',

    // Backgrounds
    background: '#F2F2F7',
    surface: '#FFFFFF',
    card: '#FFFFFF',
    elevated: '#FFFFFF',

    // Text
    text: '#000000',
    textSecondary: '#3C3C43',
    textTertiary: '#8E8E93',
    textMuted: '#AEAEB2',
    textInverse: '#FFFFFF',

    // Semantic Colors
    success: '#34C759',
    successLight: '#34C75920',
    warning: '#FF9500',
    warningLight: '#FF950020',
    error: '#FF3B30',
    errorLight: '#FF3B3020',
    info: '#007AFF',
    infoLight: '#007AFF20',

    // Borders & Dividers
    border: '#C6C6C8',
    borderLight: '#E5E5EA',
    divider: '#C6C6C8',
    separator: '#3C3C4330',

    // System
    tint: '#8B0000',
    tabIconDefault: '#8E8E93',
    tabIconSelected: '#8B0000',

    // Gradients
    gradientStart: '#8B0000',
    gradientEnd: '#5C0000',

    // Overlay
    overlay: 'rgba(0, 0, 0, 0.4)',
    modalBackground: 'rgba(0, 0, 0, 0.5)',
  },
  dark: {
    // Primary - WMSU Crimson Red (adjusted for dark mode)
    primary: '#FF4444',
    primaryLight: '#FF6B6B',
    primaryDark: '#CC0000',
    primaryMuted: '#FF444430',

    // Secondary - Gold/Accent
    secondary: '#FFD700',
    secondaryLight: '#FFEC8B',
    secondaryDark: '#DAA520',

    // Backgrounds
    background: '#000000',
    surface: '#1C1C1E',
    card: '#2C2C2E',
    elevated: '#3A3A3C',

    // Text
    text: '#FFFFFF',
    textSecondary: '#EBEBF5',
    textTertiary: '#8E8E93',
    textMuted: '#636366',
    textInverse: '#000000',

    // Semantic Colors
    success: '#30D158',
    successLight: '#30D15830',
    warning: '#FF9F0A',
    warningLight: '#FF9F0A30',
    error: '#FF453A',
    errorLight: '#FF453A30',
    info: '#0A84FF',
    infoLight: '#0A84FF30',

    // Borders & Dividers
    border: '#38383A',
    borderLight: '#48484A',
    divider: '#38383A',
    separator: '#54545830',

    // System
    tint: '#FF4444',
    tabIconDefault: '#8E8E93',
    tabIconSelected: '#FF4444',

    // Gradients
    gradientStart: '#CC0000',
    gradientEnd: '#8B0000',

    // Overlay
    overlay: 'rgba(0, 0, 0, 0.6)',
    modalBackground: 'rgba(0, 0, 0, 0.7)',
  },
} as const;

export type ColorScheme = keyof typeof Colors;
export type ThemeColors = typeof Colors.light;
