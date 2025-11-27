/**
 * LoadingSpinner Component
 * Centered loading indicator with optional message
 */

import React from 'react';
import { View, ActivityIndicator, StyleSheet } from 'react-native';
import { useTheme } from '../../context/ThemeContext';
import { Layout } from '../../constants/Layout';
import { ThemedText } from './ThemedText';

interface LoadingSpinnerProps {
  size?: 'small' | 'large';
  message?: string;
  fullScreen?: boolean;
}

export function LoadingSpinner({
  size = 'large',
  message,
  fullScreen = false,
}: LoadingSpinnerProps) {
  const { colors } = useTheme();

  const content = (
    <View style={styles.content}>
      <ActivityIndicator size={size} color={colors.primary} />
      {message && (
        <ThemedText
          variant="subheadline"
          color="secondary"
          style={styles.message}
        >
          {message}
        </ThemedText>
      )}
    </View>
  );

  if (fullScreen) {
    return (
      <View style={[styles.fullScreen, { backgroundColor: colors.background }]}>
        {content}
      </View>
    );
  }

  return content;
}

const styles = StyleSheet.create({
  content: {
    alignItems: 'center',
    justifyContent: 'center',
    padding: Layout.spacing.lg,
  },
  fullScreen: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
  },
  message: {
    marginTop: Layout.spacing.md,
    textAlign: 'center',
  },
});

export default LoadingSpinner;
