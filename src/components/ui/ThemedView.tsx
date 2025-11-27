/**
 * ThemedView Component
 * A View component that automatically uses theme-aware background colors
 */

import React from 'react';
import { View, ViewProps, StyleSheet } from 'react-native';
import { useTheme } from '../../context/ThemeContext';

interface ThemedViewProps extends ViewProps {
  variant?: 'default' | 'surface' | 'card' | 'elevated';
}

export function ThemedView({ 
  style, 
  variant = 'default',
  ...props 
}: ThemedViewProps) {
  const { colors } = useTheme();

  const backgroundColor = {
    default: colors.background,
    surface: colors.surface,
    card: colors.card,
    elevated: colors.elevated,
  }[variant];

  return (
    <View 
      style={[{ backgroundColor }, style]} 
      {...props} 
    />
  );
}

export default ThemedView;
